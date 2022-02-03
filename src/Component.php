<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\BaseComponent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class Component extends BaseComponent
{
    protected Filesystem $filesystem;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->filesystem = new Filesystem();
    }

    protected function run(): void
    {
        $dataDir = $this->getDataDir();
        $config = $this->getConfig();
        $gitRepositoryUrl = $config->getGitRepositoryUrl();

        $this->runProcessInDataDir(['git', 'clone', $gitRepositoryUrl]);

        $projectPath = $this->getProjectPath($dataDir, $gitRepositoryUrl);
        $this->addProfileYamlToProject($projectPath, $config->getAuthorization()['workspace']);

        $files = array_diff(scandir($projectPath) ?: [], ['.', '..']);
        foreach ($files as $file) {
            echo $file . PHP_EOL;
        }

        readfile(sprintf("%s/.dbt/profile.yml", $projectPath));
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

    protected function runProcessInDataDir(array $command): Process
    {
        $process = new Process($command, $this->getDataDir());
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process;
    }

    protected function createDbtFolderIfNotExist(string $projectPath): void
    {
        $dbtFolderPath = sprintf("%s/.dbt", $projectPath);
        try {
            if (!$this->filesystem->exists($dbtFolderPath)) {
                $this->filesystem->mkdir($dbtFolderPath);
            }
        } catch (IOExceptionInterface $e) {
            echo sprintf('An error occurred while creating directory %s', $dbtFolderPath);
        }
    }

    protected function addProfileYamlToProject(string $projectPath, array $workspace): void
    {
        $this->createDbtFolderIfNotExist($projectPath);
        $dbtProjectYaml = Yaml::parseFile($projectPath . '/dbt_project.yml');
        $this->filesystem->dumpFile(
            sprintf("%s/.dbt/profile.yml", $projectPath),
            Yaml::dump([
                $dbtProjectYaml['profile'] => [
                    'target' => 'dev',
                    'outputs' => ['dev' => $workspace],
                ]
            ], 4)
        );
    }

    protected function getProjectPath(string $dataDir, string $gitRepositoryUrl): string
    {
        $explodedUrl = explode('/', $gitRepositoryUrl);
        return sprintf("%s/%s", $dataDir, pathinfo(end($explodedUrl), PATHINFO_FILENAME));
    }
}
