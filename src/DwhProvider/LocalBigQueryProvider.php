<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\BigQueryDbtSourcesYaml;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\DbtSourcesYaml;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\Core\Exception\ServiceException;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client;
use Keboola\Temp\Temp;
use Psr\Log\LoggerInterface;
use Retry\BackOff\FixedBackOffPolicy;
use Retry\Policy\SimpleRetryPolicy;
use Retry\RetryProxy;
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

    protected const DATASET_CHECK_MAX_ATTEMPTS = 10;
    protected const DATASET_CHECK_RETRY_DELAY_MS = 3000;

    /**
     * @param array<int, string> $configurationNames
     * @throws \Keboola\Component\UserException
     */
    public function createDbtYamlFiles(string $profilesPath, array $configurationNames = []): void
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
            $profilesPath,
            $this->getOutputs($configurationNames, self::getDbtParams()),
        );
        $this->setEnvVars();
        $this->waitForDatasetAccessibility();

        if ($this->config->generateSources()) {
            $this->createSourceFileService->dumpYaml(
                $this->projectPath,
                $tablesData,
                $this->config->getFreshness(),
            );
        }
    }

    /**
     * Verifies the workspace dataset is writable by the workspace service account.
     *
     * After workspace creation, GCP IAM permissions may not be immediately effective
     * due to eventual consistency. This pre-flight check retries a dataset write operation
     * to ensure dbt won't fail with a 403 error on CREATE SCHEMA IF NOT EXISTS.
     *
     * Uses dataset->update() (datasets.patch API) which requires bigquery.datasets.update
     * permission — the same IAM role that grants bigquery.datasets.create needed by dbt.
     * A read-only check (dataset->reload / datasets.get) is insufficient because read
     * permissions propagate faster than write permissions in GCP IAM.
     *
     * @throws \Keboola\Component\UserException
     */
    protected function waitForDatasetAccessibility(): void
    {
        $workspace = $this->config->getAuthorization()['workspace'];
        $datasetName = $workspace['schema'];

        $dataset = $this->createBigQueryDataset($workspace, $datasetName);

        $retryPolicy = new SimpleRetryPolicy(self::DATASET_CHECK_MAX_ATTEMPTS, [ServiceException::class]);
        $backOffPolicy = new FixedBackOffPolicy(self::DATASET_CHECK_RETRY_DELAY_MS);
        $retryProxy = new RetryProxy($retryPolicy, $backOffPolicy, $this->logger);

        try {
            $retryProxy->call(function () use ($dataset): void {
                $dataset->update([]);
            });
            $this->logger->info(sprintf(
                'Workspace dataset "%s" is accessible (attempt %d/%d).',
                $datasetName,
                $retryProxy->getTryCount(),
                self::DATASET_CHECK_MAX_ATTEMPTS,
            ));
        } catch (ServiceException $e) {
            throw new UserException(sprintf(
                'Workspace dataset "%s" is not accessible after %d attempts: %s',
                $datasetName,
                self::DATASET_CHECK_MAX_ATTEMPTS,
                $e->getMessage(),
            ), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $workspace
     */
    protected function createBigQueryDataset(array $workspace, string $datasetName): Dataset
    {
        $bqClient = new BigQueryClient([
            'keyFile' => $workspace['credentials'],
            'location' => $workspace['region'],
        ]);

        return $bqClient->dataset($datasetName);
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

    public function getDwhConnectionType(): DwhConnectionTypeEnum
    {
        return DwhConnectionTypeEnum::LOCAL;
    }
}
