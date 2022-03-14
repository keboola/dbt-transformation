<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;

class Component extends BaseComponent
{
    private DbtSourceYamlCreateService $createSourceFileService;
    private DbtProfilesYamlCreateService $createProfilesFileService;
    private CloneRepositoryService $cloneRepositoryService;
    private OutputMappingService $outputMappingService;
    private string $projectPath;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->createProfilesFileService = new DbtProfilesYamlCreateService;
        $this->createSourceFileService = new DbtSourceYamlCreateService;
        $this->cloneRepositoryService = new CloneRepositoryService;
        $this->outputMappingService = new OutputMappingService($logger, $this->getConfig());
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function run(): void
    {
        $dataDir = $this->getDataDir();
        $config = $this->getConfig();
        $gitRepositoryUrl = $config->getGitRepositoryUrl();

        $inputTables = $config->getInputTables();
        if (!count($inputTables)) {
            throw new UserException('There are no tables on Input Mapping.');
        }

        $this->cloneRepository($config, $gitRepositoryUrl);

        $this->setProjectPath($dataDir);
        $this->createDbtYamlFiles($config);

        (new DbtRunService($this->projectPath))->run($config->getModelNames());

        if ($config->showSqls()) {
            $sqls = (new ParseLogFileService(sprintf('%s/logs/dbt.log', $this->projectPath)))->getSqls();
            foreach ($sqls as $sql) {
                $this->getLogger()->info($sql);
            }
        }

        if (!empty($config->getExpectedOutputTables())) {
            $this->outputMappingService->runExtractorJob();
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

    protected function createDbtYamlFiles(Config $config): void
    {
        $workspace = $config->getAuthorization()['workspace'];
        $this->createProfilesFileService->dumpYaml(
            $this->projectPath,
            sprintf('%s/dbt_project.yml', $this->projectPath),
            $workspace
        );

        $this->createSourceFileService->dumpYaml(
            $this->projectPath,
            $config->getDbtSourceName(),
            $workspace,
            $config->getInputTables()
        );
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
}
