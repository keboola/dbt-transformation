<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\Configuration\ConfigDefinition;
use DbtTransformation\Configuration\SyncAction\DbtCompileDefinition;
use DbtTransformation\Configuration\SyncAction\DbtDocsDefinition;
use DbtTransformation\Configuration\SyncAction\DbtRunResultsDefinition;
use DbtTransformation\Configuration\SyncAction\GitRepositoryDefinition;
use DbtTransformation\DwhProvider\DwhProviderFactory;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\DbtSourcesYaml;
use DbtTransformation\FileDumper\OutputManifest;
use DbtTransformation\FileDumper\OutputManifest\DbtManifestParser;
use DbtTransformation\Helper\DbtCompileHelper;
use DbtTransformation\Helper\DbtDocsHelper;
use DbtTransformation\Helper\ParseDbtOutputHelper;
use DbtTransformation\Helper\ParseLogFileHelper;
use DbtTransformation\Service\ArtifactsService;
use DbtTransformation\Service\DbtService;
use DbtTransformation\Service\GitRepositoryService;
use Keboola\Component\BaseComponent;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\SnowflakeDbAdapter\Connection;
use Keboola\StorageApi\Client as StorageClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class Component extends BaseComponent
{
    public const COMPONENT_ID = 'keboola.dbt-transformation';

    private DbtSourcesYaml $createSourceFileService;
    private DbtProfilesYaml $createProfilesFileService;
    private GitRepositoryService $gitRepositoryService;
    private string $projectPath;
    private StorageClient $storageClient;
    private ArtifactsService $artifacts;
    private DwhProviderFactory $dwhProviderFactory;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->createProfilesFileService = new DbtProfilesYaml;
        $this->createSourceFileService = new DbtSourcesYaml;
        $this->gitRepositoryService = new GitRepositoryService($this->getDataDir());
        $this->storageClient = new StorageClient([
            'url' => $this->getConfig()->getStorageApiUrl(),
            'token' => $this->getConfig()->getStorageApiToken(),
        ]);
        $this->artifacts = new ArtifactsService($this->storageClient, $this->getDataDir());
        $this->setProjectPath($this->getDataDir());
        $this->dwhProviderFactory = new DwhProviderFactory(
            $this->createSourceFileService,
            $this->createProfilesFileService,
            $this->getLogger()
        );
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function run(): void
    {
        $config = $this->getConfig();
        $this->cloneRepository($config);

        $provider = $this->dwhProviderFactory->getProvider($config, $this->projectPath);
        $provider->createDbtYamlFiles();

        $executeSteps = $config->getExecuteSteps();
        array_unshift($executeSteps, 'dbt deps');

        foreach ($executeSteps as $step) {
            $this->executeStep($step);
        }
        if ($config->showSqls()) {
            $this->logExecutedSqls();
        }

        if (!$config->hasRemoteDwh()) {
            $this->getOutputManifest($config->getWorkspaceCredentials())->dump();
        }
    }

    /**
     * @param array<string, string> $workspaceCredentials
     * @throws \Keboola\SnowflakeDbAdapter\Exception\SnowflakeDbAdapterException
     */
    public function getOutputManifest(array $workspaceCredentials): OutputManifest
    {
        $manifestManager = new ManifestManager($this->getDataDir());
        $manifestConverter = new DbtManifestParser($this->projectPath);
        $connectionConfig = array_intersect_key(
            $workspaceCredentials,
            array_flip(['host', 'warehouse', 'database', 'user', 'password'])
        );
        $connection = new Connection($connectionConfig);

        /** @var array<string, array<string, bool>> $dbtProjectYaml */
        $dbtProjectYaml = Yaml::parseFile(sprintf('%s/dbt_project.yml', $this->projectPath));
        $quoteIdentifier = $dbtProjectYaml['quoting']['identifier'] ?? false;

        return new OutputManifest(
            $workspaceCredentials,
            $connection,
            $manifestManager,
            $manifestConverter,
            $quoteIdentifier
        );
    }

    public function getConfig(): Config
    {
        /** @var Config $config */
        $config = parent::getConfig();
        return $config;
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        $configRaw = $this->getRawConfig();
        $action = $configRaw['action'] ?? 'run';

        switch ($action) {
            case 'dbtCompile':
                return DbtCompileDefinition::class;
            case 'dbtDocs':
                return DbtDocsDefinition::class;
            case 'dbtRunResults':
                return DbtRunResultsDefinition::class;
            case 'gitRepository':
                return GitRepositoryDefinition::class;
            case 'run':
            default:
                return ConfigDefinition::class;
        }
    }

    protected function setProjectPath(string $dataDir): void
    {
        $this->projectPath = sprintf('%s/%s', $dataDir, 'dbt-project');
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function cloneRepository(Config $config): void
    {
        $this->gitRepositoryService->clone(
            $config->getGitRepositoryUrl(),
            $config->getGitRepositoryBranch(),
            $config->getGitRepositoryUsername(),
            $config->getGitRepositoryPassword()
        );

        $branch = $this->gitRepositoryService->getCurrentBranch($this->projectPath);

        $this->getLogger()->info(sprintf(
            'Successfully cloned repository %s from branch %s (%s)',
            $config->getGitRepositoryUrl(),
            $branch['name'],
            $branch['ref']
        ));
    }

    protected function logExecutedSqls(): void
    {
        $sqls = (new ParseLogFileHelper(sprintf('%s/logs/dbt.log', $this->projectPath)))->getSqls();
        $this->getLogger()->info('Executed SQLs:');
        foreach ($sqls as $sql) {
            $this->getLogger()->info($sql);
        }
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function executeStep(string $step): void
    {
        $this->getLogger()->info(sprintf('Executing command "%s"', $step));
        $dbtService = new DbtService($this->projectPath);
        $output = $dbtService->runCommand($step);

        foreach (ParseDbtOutputHelper::getMessagesFromOutput($output) as $log) {
            $this->getLogger()->info($log);
        }

        if ($step === DbtService::COMMAND_DEBUG) {
            $lines = explode(PHP_EOL, $output);
            array_shift($lines); // remove the first json line
            foreach ($lines as $log) {
                $this->getLogger()->info($log);
            }
        }

        if ($step !== DbtService::COMMAND_DEPS && $step !== DbtService::COMMAND_DEBUG) {
            $this->artifacts->writeResults($this->projectPath, $step);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getSyncActions(): array
    {
        return [
            'dbtDocs' => 'actionDbtDocs',
            'dbtRunResults' => 'actionDbtRunResults',
            'dbtCompile' => 'actionDbtCompile',
            'gitRepository' => 'actionGitRepository',
        ];
    }

    /**
     * @return array<string, string>
     * @throws \Keboola\Component\UserException
     */
    protected function actionDbtDocs(): array
    {
        $componentId = getenv('KBC_COMPONENTID') ?: self::COMPONENT_ID;
        $configId = $this->getConfig()->getConfigId();
        $branchId = $this->getConfig()->getBranchId();

        $this->artifacts->downloadLastRun($componentId, $configId, $branchId);
        $this->artifacts->checkIfCorrectStepIsDownloaded(DbtService::COMMAND_DOCS_GENERATE);

        $html = $this->artifacts->readFromFile(DbtService::COMMAND_DOCS_GENERATE, 'index.html');
        $manifest = $this->artifacts->readFromFile(DbtService::COMMAND_DOCS_GENERATE, 'manifest.json');
        $catalog = $this->artifacts->readFromFile(DbtService::COMMAND_DOCS_GENERATE, 'catalog.json');

        return [
            'html' => DbtDocsHelper::mergeHtml($html, $catalog, $manifest),
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function actionDbtRunResults(): array
    {
        $componentId = getenv('KBC_COMPONENTID') ?: self::COMPONENT_ID;
        $configId = $this->getConfig()->getConfigId();
        $branchId = $this->getConfig()->getBranchId();

        $this->artifacts->downloadLastRun($componentId, $configId, $branchId);

        $manifestJson = $this->artifacts->readFromFile(DbtService::COMMAND_RUN, 'manifest.json');
        $manifest = (array) json_decode($manifestJson, true, 512, JSON_THROW_ON_ERROR);
        $runResultsJson = $this->artifacts->readFromFile(DbtService::COMMAND_RUN, 'run_results.json');
        /** @var array<string, array<string, mixed>> $runResults */
        $runResults = (array) json_decode($runResultsJson, true, 512, JSON_THROW_ON_ERROR);

        return [
            'modelTiming' => DbtDocsHelper::getModelTiming($manifest, $runResults),
        ];
    }

    /**
     * @return array<string, array<int|string, string|false>>
     * @throws \Keboola\Component\UserException
     */
    protected function actionDbtCompile(): array
    {
        $componentId = getenv('KBC_COMPONENTID') ?: self::COMPONENT_ID;
        $configId = $this->getConfig()->getConfigId();
        $branchId = $this->getConfig()->getBranchId();
        $this->artifacts->downloadLastRun($componentId, $configId, $branchId);
        $runArtifactPath = $this->artifacts->getDownloadDir() . '/' . DbtService::COMMAND_RUN;

        return [
            'compiled' => DbtCompileHelper::getCompiledSqlFiles($runArtifactPath),
        ];
    }

    /**
     * @return array<string, array<string, array<int, array<string, array<string, string>|string>>|string>>
     * @throws \Keboola\Component\UserException
     */
    protected function actionGitRepository(): array
    {
        $config = $this->getConfig();

        $this->gitRepositoryService->clone(
            $config->getGitRepositoryUrl(),
            $config->getGitRepositoryBranch(),
            $config->getGitRepositoryUsername(),
            $config->getGitRepositoryPassword()
        );

        $branches = $this->gitRepositoryService->listRemoteBranches($this->projectPath);

        return [
            'repository' => [
                'url' => $config->getGitRepositoryUrl(),
                'branches' => $branches,
            ],
        ];
    }
}
