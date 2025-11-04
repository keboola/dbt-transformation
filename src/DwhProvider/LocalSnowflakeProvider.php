<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\DbtSourcesYaml;
use DbtTransformation\FileDumper\SnowflakeDbtSourcesYaml;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LocalSnowflakeProvider extends DwhProvider implements DwhProviderInterface
{
    public const STRING_TO_REMOVE_FROM_HOST = '.snowflakecomputing.com';

    protected DbtSourcesYaml $createSourceFileService;
    protected DbtProfilesYaml $createProfilesFileService;
    protected Config $config;
    protected string $projectPath;
    protected LoggerInterface $logger;
    /** @var array<int, int> */
    protected array $projectIds = [];

    public function __construct(
        SnowflakeDbtSourcesYaml $createSourceFileService,
        DbtProfilesYaml $createProfilesFileService,
        LoggerInterface $logger,
        Config $config,
        string $projectPath,
    ) {
        $this->createProfilesFileService = $createProfilesFileService;
        $this->createSourceFileService = $createSourceFileService;
        $this->logger = $logger;
        $this->config = $config;
        $this->projectPath = $projectPath;
    }

    /**
     * @param array<int, string> $configurationNames
     * @throws \Keboola\Component\UserException
     */
    public function createDbtYamlFiles(string $profilesPath, array $configurationNames = []): void
    {
        $workspace = $this->config->getAuthorization()['workspace'];
        $hasPassword = isset($workspace['password']) || isset($workspace['#password']);
        $hasPrivateKey = isset($workspace['privateKey']);

        if (!$hasPassword && !$hasPrivateKey) {
            throw new UserException(
                'Snowflake workspace configuration must include either password or privateKey for authentication',
            );
        }

        if ($hasPassword && $hasPrivateKey) {
            throw new UserException(
                'Snowflake workspace configuration cannot include both password and privateKey - ' .
                'choose one authentication method',
            );
        }

        $tablesData = [];
        if ($this->config->generateSources()) {
            $client = new Client([
                'url' => $this->config->getStorageApiUrl(),
                'token' => $this->config->getStorageApiToken(),
            ]);

            $inputTables = $this->config->getStorageInputTables();
            foreach ($client->listBuckets() as $bucket) {
                $tables = $client->listTables($bucket['id']);
                foreach ($tables as $table) {
                    if (empty($inputTables) || in_array($table['id'], $inputTables)) {
                        $bucketId = (string) ($bucket['sourceBucket']['id'] ?? $bucket['id']);
                        $tablesData[$bucketId]['tables'][] = $table;
                        if (isset($bucket['sourceBucket']['project']['id'])) {
                            $sourceProjectId = (int) $bucket['sourceBucket']['project']['id'];
                            $this->projectIds[$sourceProjectId] = $sourceProjectId;
                            $tablesData[$bucketId]['projectId'] = $sourceProjectId;
                        }
                    }
                }
            }
        }

        $this->setEnvVars();

        $this->createProfilesFileService->dumpYaml(
            $this->projectPath,
            $profilesPath,
            $this->getOutputs($configurationNames, self::getDbtParams(), $this->projectIds),
        );

        if ($this->config->generateSources()) {
            $this->createSourceFileService->dumpYaml(
                $this->projectPath,
                $tablesData,
                $this->config->getFreshness(),
            );
        }
    }

    protected function setEnvVars(): void
    {
        $workspace = $this->config->getAuthorization()['workspace'];
        $workspace['type'] = 'snowflake';

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_SCHEMA=%s', $workspace['schema']));
        putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['database']));
        foreach ($this->projectIds as $projectId) {
            $stackPrefix = strtok($workspace['database'], '_');
            putenv(sprintf('DBT_KBC_PROD_%d_DATABASE=%s_%d', $projectId, $stackPrefix, $projectId));
        }
        putenv(sprintf('DBT_KBC_PROD_WAREHOUSE=%s', $workspace['warehouse']));
        $account = str_replace(self::STRING_TO_REMOVE_FROM_HOST, '', $workspace['host']);
        putenv(sprintf('DBT_KBC_PROD_ACCOUNT=%s', $account));
        putenv(sprintf('DBT_KBC_PROD_USER=%s', $workspace['user']));

        $privateKey = $workspace['privateKey'] ?? null;
        if ($privateKey !== null) {
            putenv(sprintf('DBT_KBC_PROD_PRIVATE_KEY=%s', $privateKey));
        } else {
            putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace['password'] ?? $workspace['#password']));
        }

        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $this->config->getThreads()));
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        $dbtParams = [
            'type',
            'user',
            'schema',
            'warehouse',
            'database',
            'account',
            'threads',
        ];

        if (getenv('DBT_KBC_PROD_PRIVATE_KEY') !== false) {
            $dbtParams[] = 'private_key';
        } else {
            $dbtParams[] = 'password';
        }

        return $dbtParams;
    }

    public function getDwhConnectionType(): DwhConnectionTypeEnum
    {
        return DwhConnectionTypeEnum::LOCAL;
    }
}
