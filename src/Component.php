<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use RuntimeException;
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

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function run(): void
    {
        $dataDir = $this->getDataDir();
        $config = $this->getConfig();
        $gitRepositoryUrl = $config->getGitRepositoryUrl();

        try {
            $this->runProcessInDataDir(['git', 'clone', $gitRepositoryUrl]);
        } catch (ProcessFailedException $e) {
            throw new UserException(sprintf('Failed to clone your repository: %s', $gitRepositoryUrl));
        }

        $projectPath = $this->getProjectPath($dataDir, $gitRepositoryUrl);
        $this->addProfileYamlToProject($projectPath, $config->getAuthorization()['workspace']);

        $files = array_diff(scandir($projectPath) ?: [], ['.', '..']);
        foreach ($files as $file) {
            echo $file . PHP_EOL;
        }

        readfile(sprintf('%s/.dbt/profile.yml', $projectPath));
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
     * @param string[] $workspace
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

    protected function createDbtFolderIfNotExist(string $projectPath): void
    {
        $dbtFolderPath = sprintf('%s/.dbt', $projectPath);
        try {
            if (!$this->filesystem->exists($dbtFolderPath)) {
                $this->filesystem->mkdir($dbtFolderPath);
            }
        } catch (IOExceptionInterface $e) {
            throw new RuntimeException(sprintf('An error occurred while creating directory %s', $dbtFolderPath));
        }
    }

    /**
     * @param string[] $workspace
     * @throws \Keboola\Component\UserException
     */
    protected function addProfileYamlToProject(string $projectPath, array $workspace): void
    {
        $this->createDbtFolderIfNotExist($projectPath);
        $dbtProjectYamlPath = $projectPath . '/dbt_project.yml';
        if (!$this->filesystem->exists($dbtProjectYamlPath)) {
            throw new UserException(sprintf('Missing file %s in your project', $dbtProjectYamlPath));
        }
        $dbtProjectYaml = Yaml::parseFile($dbtProjectYamlPath);
        $this->filesystem->dumpFile(
            sprintf('%s/.dbt/profile.yml', $projectPath),
            Yaml::dump([
                $dbtProjectYaml['profile'] => [
                    'target' => 'dev',
                    'outputs' => ['dev' => $workspace],
                ],
            ], 4)
        );
    }

    protected function getProjectPath(string $dataDir, string $gitRepositoryUrl): string
    {
        $explodedUrl = explode('/', $gitRepositoryUrl);
        return sprintf('%s/%s', $dataDir, pathinfo(end($explodedUrl), PATHINFO_FILENAME));
    }
}
