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
    private DbtYamlCreateService\DbtProfilesYamlCreateService $createProfilesFileService;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->createProfilesFileService = new DbtYamlCreateService\DbtProfilesYamlCreateService;
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
            $this->runProcess(['git', 'clone', $gitRepositoryUrl], $this->getDataDir());
        } catch (ProcessFailedException $e) {
            throw new UserException(sprintf('Failed to clone your repository: %s', $gitRepositoryUrl));
        }

        $projectPath = $this->getProjectPath($dataDir, $gitRepositoryUrl);
        $workspace = $config->getAuthorization()['workspace'];
        $this->createProfilesFileService->dumpYaml(
            $projectPath,
            sprintf('%s/dbt_project.yml', $projectPath),
            $workspace
        );
        $this->createSourceFileService->dumpYaml($projectPath, $workspace, $config->getInputTables());

        try {
            $this->runProcess(['dbt', 'run', '--profiles-dir', sprintf('%s/.dbt/', $projectPath)], $projectPath);
        } catch (ProcessFailedException $e) {
            throw new UserException($e->getMessage());
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

    /**
     * @param string[] $command
     */
    protected function runProcess(array $command, string $cwd): Process
    {
        $process = new Process($command, $cwd);
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
