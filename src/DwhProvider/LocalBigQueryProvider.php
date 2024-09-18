<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\BigQueryDbtSourcesYaml;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\DbtSourcesYaml;
use Keboola\StorageApi\Client;
use Keboola\Temp\Temp;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LocalBigQueryProvider extends DwhProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'bigquery';

    protected DbtSourcesYaml $createSourceFileService;
    protected DbtProfilesYaml $createProfilesFileService;
    protected Config $config;
    protected string $projectPath;
    protected LoggerInterface $logger;
    private Temp $temp;

    public function __construct(
        BigQueryDbtSourcesYaml $createSourceFileService,
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
        $this->temp = new Temp('dbt-big-query-local');
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
                    }
                }
            }
        }

        $this->createProfilesFileService->dumpYaml(
            $this->projectPath,
            $this->getOutputs($configurationNames, self::getDbtParams()),
        );
        $this->setEnvVars();

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
        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', self::DWH_PROVIDER_TYPE));
        putenv(sprintf('DBT_KBC_PROD_METHOD=%s', 'service-account'));
        putenv(sprintf('DBT_KBC_PROD_PROJECT=%s', $workspace['credentials']['project_id']));
        putenv(sprintf('DBT_KBC_PROD_LOCATION=%s', $workspace['region']));
        putenv(sprintf('DBT_KBC_PROD_DATASET=%s', $workspace['schema']));
        // create temp file with key
        $tmpKeyFile = $this->temp->createFile('key');
        file_put_contents($tmpKeyFile->getPathname(), json_encode($workspace['credentials']));
        putenv(sprintf('DBT_KBC_PROD_KEYFILE=%s', $tmpKeyFile));
    }

    /**
     * @return array<int, string>
     */
    public static function getConnectionParams(): array
    {
        return [
            'type',
            'method',
            'project',
            'dataset',
            'keyfile',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        return [
            'type',
            'method',
            'project',
            'location',
            'dataset',
            'keyfile',
        ];
    }

    public function __destruct()
    {
        $this->temp->remove();
    }
}
