<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper\OutputManifest;

use Google\Cloud\BigQuery\BigQueryClient;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\TableBackendUtils\Table\Bigquery\BigqueryTableDefinition;
use Keboola\TableBackendUtils\Table\Bigquery\BigqueryTableReflection;
use Keboola\TableBackendUtils\TableNotExistsReflectionException;
use Psr\Log\LoggerInterface;

class OutputManifestBigQuery extends OutputManifest implements OutputManifestInterface
{
    private BigQueryClient $client;
    private LoggerInterface $logger;
    private string $schema;

    /**
     * @param array<string, string> $databaseConfig
     */
    public function __construct(
        array $databaseConfig,
        BigQueryClient $client,
        ManifestManager $manifestManager,
        DbtManifestParser $dbtManifestParser,
        LoggerInterface $logger,
        bool $legacyFormat = true,
        bool $quoteIdentifier = false,
    ) {
        parent::__construct($manifestManager, $dbtManifestParser, 'bigquery', $legacyFormat, $quoteIdentifier);
        $this->client = $client;
        $this->logger = $logger;
        $this->schema = $databaseConfig['schema'];
    }

    /**
     * @param array<string> $tables
     * @return BigqueryTableDefinition[]
     */

    protected function getTables(array $tables): array
    {
        if (count($tables) === 0) {
            return [];
        }

        $defs = [];
        foreach ($tables as $tableName) {
            try {
                $defs[] = $this->getDefinition($tableName);
            } catch (TableNotExistsReflectionException) {
                // do nothing for models/tables not existing in the DB
                $this->logger->warning(
                    sprintf('Table "%s" specified in dbt manifest was not found in the database.', $tableName),
                );
            }
        }

        return $defs;
    }

    protected function getDefinition(string $tableName): BigqueryTableDefinition
    {
        $ref = new BigqueryTableReflection(
            $this->client,
            $this->schema,
            $tableName,
        );

        return new BigqueryTableDefinition(
            $this->schema,
            $tableName,
            false,
            $ref->getColumnsDefinitions(),
            [],
        );
    }
}
