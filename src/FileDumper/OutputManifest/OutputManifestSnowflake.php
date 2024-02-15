<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper\OutputManifest;

use Keboola\Component\Manifest\ManifestManager;
use Keboola\Datatype\Definition\Snowflake as SnowflakeDatatype;
use Keboola\SnowflakeDbAdapter\Connection;
use Keboola\SnowflakeDbAdapter\Exception\RuntimeException;
use Keboola\TableBackendUtils\Column\ColumnCollection;
use Keboola\TableBackendUtils\Column\Snowflake\SnowflakeColumn;
use Keboola\TableBackendUtils\Escaping\Snowflake\SnowflakeQuote;
use Keboola\TableBackendUtils\Table\Snowflake\SnowflakeTableDefinition;
use Psr\Log\LoggerInterface;

class OutputManifestSnowflake extends OutputManifest implements OutputManifestInterface
{
    /** @var array<string, string> */
    private array $databaseConfig;
    private Connection $connection;
    private LoggerInterface $logger;
    private bool $quoteIdentifier;

    /**
     * @param array<string, string> $databaseConfig
     */
    public function __construct(
        array $databaseConfig,
        Connection $connection,
        ManifestManager $manifestManager,
        DbtManifestParser $dbtManifestParser,
        LoggerInterface $logger,
        bool $quoteIdentifier = false,
    ) {
        parent::__construct(
            $manifestManager,
            $dbtManifestParser,
            SnowflakeDatatype::METADATA_BACKEND,
            $quoteIdentifier,
        );
        $this->databaseConfig = $databaseConfig;
        $this->connection = $connection;
        $this->quoteIdentifier = $quoteIdentifier;
        $this->logger = $logger;
    }

    /**
     * @param array<string> $dbtModelNames
     * @return SnowflakeTableDefinition[]
     */
    protected function getTables(array $dbtModelNames): array
    {
        $defs = [];
        $schema = $this->databaseConfig['schema'];

        foreach ($dbtModelNames as $tableName) {
            try {
                /** @var array<array{
                 *     name: string,
                 *     kind: string,
                 *     type: string,
                 *     default: string,
                 *     "null?": string
                 * }> $columnsMeta */
                $columnsMeta = $this->connection->fetchAll(
                    sprintf(
                        'DESC TABLE %s',
                        SnowflakeQuote::createQuotedIdentifierFromParts([
                            $schema,
                            $this->quoteIdentifier ? $tableName : strtoupper($tableName),
                        ]),
                    ),
                );
            } catch (RuntimeException $e) {
                // do nothing for models/tables not existing in the DB
                $this->logger->warning(
                    sprintf('Table "%s" specified in dbt manifest was not found in the database.', $tableName),
                );
                continue;
            }

            $columns = [];
            foreach ($columnsMeta as $col) {
                if ($col['kind'] === 'COLUMN') {
                    $columns[] = SnowflakeColumn::createFromDB($col);
                }
            }

            $defs[] = new SnowflakeTableDefinition(
                $schema,
                $tableName,
                false,
                new ColumnCollection($columns),
                [],
            );
        }

        return $defs;
    }
}
