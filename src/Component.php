<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Component extends BaseComponent
{
    private DbtYamlCreateService\DbtSourceYamlCreateService $createSourceFileService;
    private DbtYamlCreateService\DbtProfileYamlCreateService $createProfileFileService;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->createProfileFileService = new DbtYamlCreateService\DbtProfileYamlCreateService;
        $this->createSourceFileService = new DbtYamlCreateService\DbtSourceYamlCreateService;
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
            throw new UserException('There are no tables on input');
        }

        try {
            $this->runProcessInDataDir(['git', 'clone', $gitRepositoryUrl]);
        } catch (ProcessFailedException $e) {
            throw new UserException(sprintf('Failed to clone your repository: %s', $gitRepositoryUrl));
        }

        $projectPath = $this->getProjectPath($dataDir, $gitRepositoryUrl);
        $workspace = $config->getAuthorization()['workspace'];
        $this->createProfileFileService->dumpYaml(
            $projectPath,
            sprintf('%s/dbt_project.yml', $projectPath),
            $workspace
        );
        $this->createSourceFileService->dumpYaml($projectPath, $workspace, $config->getInputTables());

        readfile(sprintf('%s/models/src_%s.yml', $projectPath, $workspace['schema']));
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

    /**
     * @param string[] $command
     */
    protected function runProcessInDataDir(array $command): Process
    {
        $process = new Process($command, $this->getDataDir());
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }

    protected function getProjectPath(string $dataDir, string $gitRepositoryUrl): string
    {
        $explodedUrl = explode('/', $gitRepositoryUrl);
        return sprintf('%s/%s', $dataDir, pathinfo(end($explodedUrl), PATHINFO_FILENAME));
    }
}
