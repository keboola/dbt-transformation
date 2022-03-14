<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\JobRunner\JobRunnerFactory;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client;
use Psr\Log\LoggerInterface;

class OutputMappingService
{
    private const SNOWFLAKE_EXTRACTOR_COMPONENT_NAME = 'keboola.ex-db-snowflake';

    private LoggerInterface $logger;
    private Config $config;

    public function __construct(LoggerInterface $logger, Config $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function runExtractorJob(): void
    {
        [$workspace, $schema] = $this->prepareCredentials();

        $jobRunnerFactory = JobRunnerFactory::create($this->createStorageClient(), $this->logger);
        foreach ($this->config->getExpectedOutputTables() as $outputTable) {
            $job = $jobRunnerFactory->runJob(
                self::SNOWFLAKE_EXTRACTOR_COMPONENT_NAME,
                [
                    'parameters' => [
                        'db' => $workspace,
                        'id' => 1,
                        'name' => $outputTable['source'],
                        'table' => [
                            'schema' => $schema,
                            'tableName' => $outputTable['source'],
                        ],
                        'outputTable' => $outputTable['destination'],
                    ],
                ]
            );

            if ($job['status'] === 'error') {
                throw new UserException(sprintf(
                    'Extractor job failed with following message: "%s"',
                    $job['result']['message']
                ));
            }

            if ($job['status'] !== 'success') {
                throw new UserException(sprintf(
                    'Extractor job failed with status "%s" and message: "%s"',
                    $job['status'],
                    $job['result']['message'] ?? 'No message'
                ));
            }

            $this->logger->info(sprintf('Finished extractor job "%d" succeeded', $job['id']));
        }
    }

    private function createStorageClient(): Client
    {
        $client = new Client([
            'token' => $this->config->getStorageApiToken(),
            'url' => $this->config->getStorageApiUrl(),
        ]);
        $client->setRunId($this->config->getRunId());

        return $client;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function prepareCredentials(): array
    {
        $workspace = $this->config->getAuthorization()['workspace'];
        $workspace['#password'] = $workspace['password'];
        $workspace['port'] = 443;
        $schema = $workspace['schema'];
        unset($workspace['schema'], $workspace['password']);

        return [$workspace, $schema];
    }
}
