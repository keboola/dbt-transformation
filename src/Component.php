<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Component extends BaseComponent
{
    private DbtSourceYamlCreateService $createSourceFileService;
    private DbtProfilesYamlCreateService $createProfilesFileService;
    private string $projectPath;

    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->createProfilesFileService = new DbtProfilesYamlCreateService;
        $this->createSourceFileService = new DbtSourceYamlCreateService;
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    protected function run(): void
    {
        $dataDir = $this->getDataDir();
        $config = $this->getConfig();
        $gitRepositoryUrl = $config->getGitRepositoryUrl();

        if ($config->getAuthorization()['workspace']['backend'] !== 'snowflake') {
            throw new UserException('Only Snowflake backend is supported at the moment');
        }

        $inputTables = $config->getInputTables();
        if (!count($inputTables)) {
            throw new UserException('There are no tables on Input Mapping.');
        }

        $this->cloneRepository($config, $gitRepositoryUrl);

        $this->setProjectPath($dataDir, $gitRepositoryUrl);
        $this->createDbtYamlFiles($config);

        $selectParameter = [];
        $modelNames = $config->getModelNames();
        if (!empty($modelNames)) {
            $selectParameter = ['--select', ...$modelNames];
        }

        $dbtCommand = [
            'dbt',
            '--log-format',
            'json',
            '--warn-error',
            'run',
            ...$selectParameter,
            '--profiles-dir',
            sprintf('%s/.dbt/', $this->projectPath),
        ];

        try {
            $this->runProcess($dbtCommand, $this->projectPath);
        } catch (ProcessFailedException $e) {
            throw new UserException($e->getMessage());
        }

        if ($config->showSqls()) {
            $sqls = (new ParseLogFileService(sprintf('%s/logs/dbt.log', $this->projectPath)))->getSqls();
            foreach ($sqls as $sql) {
                echo $sql . PHP_EOL;
            }
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
        $process->mustRun();

        return $process;
    }

    protected function setProjectPath(string $dataDir, string $gitRepositoryUrl): void
    {
        $explodedUrl = explode('/', $gitRepositoryUrl);
        $this->projectPath = sprintf('%s/%s', $dataDir, pathinfo(end($explodedUrl), PATHINFO_FILENAME));
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
        $branch = [];
        $gitRepositoryBranch = $config->getGitRepositoryBranch();
        if ($gitRepositoryBranch) {
            $branch = ['-b', $gitRepositoryBranch];
        }

        $gitRepositoryUsername = $config->getGitRepositoryUsername();
        $gitRepositoryPassword = $config->getGitRepositoryPassword();
        if ($gitRepositoryUsername && $gitRepositoryPassword) {
            $githubUrl = 'github.com';
            $gitRepositoryUrl = str_replace($githubUrl, sprintf(
                '%s:%s@%s',
                $gitRepositoryUsername,
                $gitRepositoryPassword,
                $githubUrl
            ), $gitRepositoryUrl);
        }

        try {
            $this->runProcess(['git', 'clone', ...$branch, $gitRepositoryUrl], $this->getDataDir());
        } catch (ProcessFailedException $e) {
            throw new UserException(sprintf('Failed to clone your repository: %s', $gitRepositoryUrl));
        }
    }
}
