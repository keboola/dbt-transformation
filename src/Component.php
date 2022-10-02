<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\ConfigDefinition\ConfigDefinition;
use DbtTransformation\ConfigDefinition\SyncAction\DbtDocsDefinition;
use DbtTransformation\ConfigDefinition\SyncAction\DbtRunResultsDefinition;
use DbtTransformation\ConfigDefinition\SyncAction\GitRepositoryDefinition;
use DbtTransformation\DwhProvider\DwhProviderFactory;
use DbtTransformation\Helper\DbtDocsHelper;
use DbtTransformation\Helper\ParseDbtOutputHelper;
use DbtTransformation\Helper\ParseLogFileHelper;
use DbtTransformation\Service\ArtifactsService;
use DbtTransformation\Service\DbtService;
use DbtTransformation\Service\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\Service\DbtYamlCreateService\DbtSourceYamlCreateService;
use DbtTransformation\Service\GitRepositoryService;
use Keboola\Component\BaseComponent;
use Keboola\StorageApi\Client as StorageClient;
use Psr\Log\LoggerInterface;

class Component extends BaseComponent
{
    public const COMPONENT_ID = 'keboola.dbt-transformation';

    private DbtSourceYamlCreateService $createSourceFileService;
    private DbtProfilesYamlCreateService $createProfilesFileService;
    private GitRepositoryService $gitRepositoryService;
    private string $projectPath;
    private StorageClient $storageClient;
    private ArtifactsService $artifacts;
    private DwhProviderFactory $dwhProviderFactory;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->createProfilesFileService = new DbtProfilesYamlCreateService;
        $this->createSourceFileService = new DbtSourceYamlCreateService;
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
        $dbtService = new DbtService($this->projectPath, $this->getConfig()->getModelNames());
        $output = $dbtService->runCommand($step);
        foreach (ParseDbtOutputHelper::getMessagesFromOutput($output) as $log) {
            $this->getLogger()->info($log);
        }
        if ($step !== 'dbt deps' && $step !== 'dbt debug') {
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
     */
    protected function actionDbtDocs(): array
    {
        $componentId = $this->getConfig()->getStringValue(['componentId']);
        $configId = $this->getConfig()->getConfigId();
        $branchId = $this->getConfig()->getBranchId();

        $this->artifacts->downloadLastRun($componentId, $configId, $branchId);

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
        $componentId = $this->getConfig()->getStringValue(['componentId']);
        $configId = $this->getConfig()->getConfigId();
        $branchId = $this->getConfig()->getBranchId();

        $this->artifacts->downloadLastRun($componentId, $configId, $branchId);

        $manifestJson = $this->artifacts->readFromFile(DbtService::COMMAND_RUN, 'manifest.json');
        $manifest = (array) json_decode($manifestJson, true, 512, JSON_THROW_ON_ERROR);
        $runResultsJson = $this->artifacts->readFromFile(DbtService::COMMAND_RUN, 'run_results.json');
        $runResults = (array) json_decode($runResultsJson, true, 512, JSON_THROW_ON_ERROR);

        return [
            'modelTiming' => DbtDocsHelper::getModelTiming($manifest, $runResults),
        ];
    }

    /**
     * @return array<string, array<int|string, string|false>>
     */
    protected function actionDbtCompile(): array
    {
        $componentId = $this->getConfig()->getStringValue(['componentId']);
        $configId = $this->getConfig()->getConfigId();
        $branchId = $this->getConfig()->getBranchId();

        $this->artifacts->downloadLastRun($componentId, $configId, $branchId);

        return [
            'compiled' => $this->artifacts->getCompiledSqlFiles(),
        ];
    }

    /**
     * @return array<string, array<string, array<int, string>|string>>
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
