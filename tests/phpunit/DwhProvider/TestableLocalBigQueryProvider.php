<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\DwhProvider\LocalBigQueryProvider;
use DbtTransformation\FileDumper\BigQueryDbtSourcesYaml;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\Core\Exception\ServiceException;
use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Retry\BackOff\NoBackOffPolicy;
use Retry\Policy\SimpleRetryPolicy;
use Retry\RetryProxy;

/**
 * Testable subclass that overrides createBigQueryDataset() to return a mock
 * and uses NoBackOffPolicy to avoid sleeping during tests.
 */
class TestableLocalBigQueryProvider extends LocalBigQueryProvider
{
    private Dataset $mockDataset;

    public function __construct(
        BigQueryDbtSourcesYaml $createSourceFileService,
        DbtProfilesYaml $createProfilesFileService,
        LoggerInterface $logger,
        Config $config,
        string $projectPath,
        Dataset $mockDataset,
    ) {
        parent::__construct($createSourceFileService, $createProfilesFileService, $logger, $config, $projectPath);
        $this->mockDataset = $mockDataset;
    }

    /**
     * @param array<string, mixed> $workspace
     */
    protected function createBigQueryDataset(array $workspace, string $datasetName): Dataset
    {
        return $this->mockDataset;
    }

    public function callWaitForDatasetAccessibility(): void
    {
        $workspace = $this->config->getAuthorization()['workspace'];
        $datasetName = $workspace['schema'];

        $dataset = $this->createBigQueryDataset($workspace, $datasetName);

        $retryPolicy = new SimpleRetryPolicy(self::DATASET_CHECK_MAX_ATTEMPTS, [ServiceException::class]);
        $backOffPolicy = new NoBackOffPolicy();
        $retryProxy = new RetryProxy($retryPolicy, $backOffPolicy, $this->logger);

        try {
            $retryProxy->call(function () use ($dataset): void {
                $dataset->reload();
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
}
