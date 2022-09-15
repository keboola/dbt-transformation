<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use DbtTransformation\DwhProvider\DwhProviderFactory;
use DbtTransformation\SyncAction\DocsHelper;
use Keboola\Component\BaseComponent;
use Keboola\StorageApi\Client as StorageClient;
use Psr\Log\LoggerInterface;

class Component extends BaseComponent
{
    public const COMPONENT_ID = 'keboola.dbt-transformation';

    public const STEP_RUN = 'dbt run';
    public const STEP_DOCS_GENERATE = 'dbt docs generate';
    public const STEP_TEST = 'dbt test';
    public const STEP_SOURCE_FRESHNESS = 'dbt source freshness';

    private DbtSourceYamlCreateService $createSourceFileService;
    private DbtProfilesYamlCreateService $createProfilesFileService;
    private CloneRepositoryService $cloneRepositoryService;
    private string $projectPath;
    private StorageClient $storageClient;
    private Artifacts $artifacts;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->createProfilesFileService = new DbtProfilesYamlCreateService;
        $this->createSourceFileService = new DbtSourceYamlCreateService;
        $this->cloneRepositoryService = new CloneRepositoryService($this->getLogger());

        $this->storageClient = new StorageClient([
            'url' => $this->getConfig()->getStorageApiUrl(),
            'token' => $this->getConfig()->getStorageApiToken(),
        ]);
        $this->artifacts = new Artifacts($this->storageClient, $this->getDataDir());
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function run(): void
    {
        $dataDir = $this->getDataDir();
        $config = $this->getConfig();
        $gitRepositoryUrl = $config->getGitRepositoryUrl();
        $this->cloneRepository($config, $gitRepositoryUrl);
        $this->setProjectPath($dataDir);

        $dwhProviderFactory = new DwhProviderFactory(
            $this->createSourceFileService,
            $this->createProfilesFileService,
            $this->getLogger()
        );
        $provider = $dwhProviderFactory->getProvider($config, $this->projectPath);
        $provider->createDbtYamlFiles();

        $dbtService = new DbtService($this->projectPath);
        $dbtService->setModelNames($config->getModelNames());

        $executeSteps = $config->getExecuteSteps();
        array_unshift($executeSteps, 'dbt deps');

        foreach ($executeSteps as $step) {
            $this->executeStep($dbtService, $step);
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
        if (($configRaw['action'] ?? 'run') !== 'run') {
            return ConfigDefinitionSyncActions::class;
        }
        return ConfigDefinition::class;
    }

    protected function setProjectPath(string $dataDir): void
    {
        $this->projectPath = sprintf('%s/%s', $dataDir, 'dbt-project');
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function cloneRepository(Config $config, string $gitRepositoryUrl): void
    {
        $this->cloneRepositoryService->clone(
            $this->getDataDir(),
            $gitRepositoryUrl,
            $config->getGitRepositoryBranch(),
            $config->getGitRepositoryUsername(),
            $config->getGitRepositoryPassword()
        );
    }

    protected function logExecutedSqls(): void
    {
        $sqls = (new ParseLogFileService(sprintf('%s/logs/dbt.log', $this->projectPath)))->getSqls();
        $this->getLogger()->info('Executed SQLs:');
        foreach ($sqls as $sql) {
            $this->getLogger()->info($sql);
        }
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function executeStep(DbtService $dbtService, string $step): void
    {
        $output = $dbtService->runCommand($step);
        foreach (ParseDbtOutputHelper::getMessagesFromOutput($output) as $log) {
            $this->getLogger()->info($log);
        }
        if ($step !== 'dbt deps') {
            $this->artifacts->uploadResults($this->projectPath, $step);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getSyncActions(): array
    {
        return [
            'dbtDocs' => 'actionDbtDocs',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function actionDbtDocs(): array
    {
        $configId = $this->getConfig()->getConfigId();
        $branchId = $this->getConfig()->getBranchId();

        $this->artifacts->downloadLastRun(self::COMPONENT_ID, $configId, $branchId);

        $html = $this->artifacts->readFromFile(self::STEP_DOCS_GENERATE, 'index.html');
        $manifest = $this->artifacts->readFromFile(self::STEP_DOCS_GENERATE, 'manifest.json');
        $catalog = $this->artifacts->readFromFile(self::STEP_DOCS_GENERATE, 'catalog.json');

        return [
            'html' => DocsHelper::mergeHtml($html, $manifest, $catalog),
        ];
    }
}
