<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\DbtSourcesYaml;
use DbtTransformation\FileDumper\SnowflakeDbtSourcesYaml;
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
    public function createDbtYamlFiles(array $configurationNames = []): void
    {
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
        putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace['password'] ?? $workspace['#password']));
        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $this->config->getThreads()));
    }

    /**
     * @return array<int, string>
     */
    public static function getRequiredConnectionParams(): array
    {
        return [
            'schema',
            'database',
            'warehouse',
            'host',
            'user',
            '#password',
            'threads',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        return [
            'type',
            'user',
            'password',
            'schema',
            'warehouse',
            'database',
            'account',
            'threads',
        ];
    }

    public function getDwhLocation(): DwhLocationEnum
    {
        return DwhLocationEnum::LOCAL;
    }
}
