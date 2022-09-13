<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use DbtTransformation\DwhProvider\DwhProviderFactory;
use Keboola\Component\BaseComponent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class Component extends BaseComponent
{
    private DbtSourceYamlCreateService $createSourceFileService;
    private DbtProfilesYamlCreateService $createProfilesFileService;
    private CloneRepositoryService $cloneRepositoryService;
    private string $projectPath;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->createProfilesFileService = new DbtProfilesYamlCreateService;
        $this->createSourceFileService = new DbtSourceYamlCreateService;
        $this->cloneRepositoryService = new CloneRepositoryService($this->getLogger());
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

    private function storeResultToArtifacts(string $step): void
    {
        $fs = new Filesystem();
        $artifactsPath = sprintf('%s/artifacts/out/current/%s', $this->getDataDir(), $step);
        $fs->mkdir($artifactsPath);
        $fs->mirror(sprintf('%s/target/', $this->projectPath), $artifactsPath);
    }

    private function readResultFromArtifacts(string $step, string $filePath): string
    {
        $artifactsPath = sprintf('%s/artifacts/in/runs/%s/%s', $this->getDataDir(), $step, $filePath);
        return file_get_contents($artifactsPath);
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
            $this->storeResultToArtifacts($step);
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
        return [
            'html' => file_get_contents(__DIR__ . '/SyncAction/index.html'),
        ];
    }
}
