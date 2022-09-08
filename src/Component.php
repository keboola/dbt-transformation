<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use Keboola\Component\BaseComponent;
use Keboola\StorageApi\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class Component extends BaseComponent
{
    public const STRING_TO_REMOVE_FROM_HOST = '.snowflakecomputing.com';

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
        $this->createDbtYamlFiles($config);

        $dbtService = new DbtService($this->projectPath);
        $dbtService->setModelNames($config->getModelNames());

        $executeSteps = $config->getExecuteSteps();
        array_unshift($executeSteps, 'dbt deps');

        if ($config->hasRemoteDwh()) {
            $dwhConfig = $config->getRemoteDwh();
            $this->getLogger()->info(sprintf('Remote %s DWH: %s', $dwhConfig['type'], $dwhConfig['host']));
        }

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
    protected function createDbtYamlFiles(Config $config): void
    {
        if ($config->hasRemoteDwh()) {
            $this->createProfilesFileService->dumpYaml($this->projectPath, [], $config->getRemoteDwh()['type']);
        } else {
            $this->createProfilesFileService->dumpYaml($this->projectPath);
        }

        $this->setEnvVars();

        $client = new Client(['url' => $config->getStorageApiUrl(), 'token' => $config->getStorageApiToken()]);
        $tables = $client->listTables();
        $tablesData = [];
        foreach ($tables as $table) {
            $tablesData[(string) $table['bucket']['id']][] = $table;
        }

        $this->createSourceFileService->dumpYaml(
            $this->projectPath,
            $tablesData
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

    private function setEnvVars(): void
    {
        $config = $this->getConfig();
        if ($config->hasRemoteDwh()) {
            $workspace = $config->getRemoteDwh();
        } else {
            $workspace = $config->getAuthorization()['workspace'];
            $workspace['type'] = 'snowflake';
        }

        putenv(sprintf('DBT_KBC_PROD_SCHEMA=%s', $workspace['schema']));
        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        if ($workspace['type'] === 'snowflake') {
            putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['database']));
            putenv(sprintf('DBT_KBC_PROD_WAREHOUSE=%s', $workspace['warehouse']));
            $account = str_replace(self::STRING_TO_REMOVE_FROM_HOST, '', $workspace['host']);
            putenv(sprintf('DBT_KBC_PROD_ACCOUNT=%s', $account));
        } elseif ($workspace['type'] === 'bigquery') {
            putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
            putenv(sprintf('DBT_KBC_PROD_METHOD=%s', $workspace['method']));
            putenv(sprintf('DBT_KBC_PROD_PROJECT=%s', $workspace['project']));
            putenv(sprintf('DBT_KBC_PROD_DATASET=%s', $workspace['dataset']));
            putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $workspace['threads']));
            // create temp file with key
            $tmpKeyFile = tempnam(__DIR__ . '/../', 'key-');
            file_put_contents($tmpKeyFile, $workspace['#key_content']);
            putenv(sprintf('DBT_KBC_PROD_KEYFILE=%s', $tmpKeyFile));
        } else {
            putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['dbname']));
            putenv(sprintf('DBT_KBC_PROD_HOST=%s', $workspace['host']));
            putenv(sprintf('DBT_KBC_PROD_PORT=%s', $workspace['port']));
        }
        putenv(sprintf('DBT_KBC_PROD_USER=%s', $workspace['user']));
        putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace['password'] ?? $workspace['#password']));
    }

    private function storeResultToArtifacts(string $step): void
    {
        $fs = new Filesystem();
        $artifactsPath = sprintf('%s/artifacts/out/current/%s', $this->getDataDir(), $step);
        $fs->mkdir($artifactsPath);
        $fs->mirror(sprintf('%s/target/', $this->projectPath), $artifactsPath);
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
}
