<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

use DbtTransformation\FileDumper\OutputManifest\DbtManifestParser;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\Component\Manifest\ManifestManager\Options\OutTableManifestOptions;
use Keboola\Datatype\Definition\Common;
use Keboola\Datatype\Definition\Snowflake as SnowflakeDatatype;
use Keboola\SnowflakeDbAdapter\Connection;
use Keboola\TableBackendUtils\Column\ColumnCollection;
use Keboola\TableBackendUtils\Column\Snowflake\SnowflakeColumn;
use Keboola\TableBackendUtils\Escaping\Snowflake\SnowflakeQuote;
use Keboola\TableBackendUtils\Table\Snowflake\SnowflakeTableDefinition;

class OutputManifest
{
    private ManifestManager $manifestManager;
    /** @var array<string, string> */
    private array $databaseConfig;
    private DbtManifestParser $dbtManifestParser;
    private Connection $connection;

    /**
     * @param array<string, string> $databaseConfig
     */
    public function __construct(
        array $databaseConfig,
        Connection $connection,
        ManifestManager $manifestManager,
        DbtManifestParser $dbtManifestParser
    ) {
        $this->manifestManager = $manifestManager;
        $this->databaseConfig = $databaseConfig;
        $this->connection = $connection;
        $this->dbtManifestParser = $dbtManifestParser;
    }

    public function dump(): void
    {
        $dbtMetadata = $this->dbtManifestParser->parse();
        $tableStructures = $this->getTables();

        foreach ($tableStructures as $tableDef) {
            $tableName = $tableDef->getTableName();
            $dbtColumnsMetadata = $dbtMetadata[$tableName]['column_metadata'] ?? [];

            $columnsMetadata = (object) [];
            /** @var SnowflakeColumn $column */
            foreach ($tableDef->getColumnsDefinitions() as $column) {
                $columnName = $column->getColumnName();
                $columnsMetadata->{$columnName} = array_merge(
                    $column->getColumnDefinition()->toMetadata(),
                    $dbtColumnsMetadata[$columnName] ?? []
                );
            }

            $tableMetadata = $dbtMetadata[$tableName]['metadata'] ?? [];
            $tableMetadata[] = [
                'key' => 'KBC.name',
                'value' => $tableName,
            ];
            // add metadata indicating that this output is snowflake native
            $tableMetadata[] = [
                'key' => Common::KBC_METADATA_KEY_BACKEND,
                'value' => SnowflakeDatatype::METADATA_BACKEND,
            ];

            $tableManifestOptions = new OutTableManifestOptions();
            $tableManifestOptions
                ->setMetadata($tableMetadata)
                ->setColumns($tableDef->getColumnsNames())
                ->setColumnMetadata($columnsMetadata)
            ;

            $this->manifestManager->writeTableManifest($tableName, $tableManifestOptions);
        }
    }

    /**
     * @return SnowflakeTableDefinition[]
     */
    private function getTables(): array
    {
        $defs = [];
        $schema = $this->databaseConfig['schema'];

        $sourceTables = $this->connection->fetchAll(
            sprintf(
                'SHOW TABLES IN %s',
                SnowflakeQuote::quoteSingleIdentifier($schema)
            )
        );

        foreach ($sourceTables as $table) {
            $tableName = $table['name'];

            /** @var array<array{
             *     name: string,
             *     kind: string,
             *     type: string,
             *     default: string,
             *     'null?': string
             * }> $columnsMeta */
            $columnsMeta = $this->connection->fetchAll(
                sprintf(
                    'DESC TABLE %s',
                    SnowflakeQuote::createQuotedIdentifierFromParts([$schema, $tableName])
                )
            );

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
                []
            );
        }

        return $defs;
    }
}
