<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

use DbtTransformation\FileDumper\OutputManifest\DbtManifestParser;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\Component\Manifest\ManifestManager\Options\OutTableManifestOptions;
use Keboola\Component\UserException;
use Keboola\Datatype\Definition\Common;
use Keboola\Datatype\Definition\Snowflake as SnowflakeDatatype;
use Keboola\SnowflakeDbAdapter\Connection;
use Keboola\SnowflakeDbAdapter\Exception\RuntimeException;
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
        $dbtModelNames = array_keys($dbtMetadata);
        $tableStructures = $this->getTables($dbtModelNames);

        foreach ($tableStructures as $tableDef) {
            $tableName = $tableDef->getTableName();
            $dbtColumnsMetadata = $dbtMetadata[$tableName]['column_metadata'] ?? [];
            /** @var array<string> $dbtPrimaryKey */
            $dbtPrimaryKey = $dbtMetadata[$tableName]['primary_key'] ?? [];

            $columnsMetadata = (object) [];
            /** @var SnowflakeColumn $column */
            foreach ($tableDef->getColumnsDefinitions() as $column) {
                $columnName = $column->getColumnName();
                $columnsMetadata->{$columnName} = array_merge(
                    $column->getColumnDefinition()->toMetadata(),
                    (array) ($dbtColumnsMetadata[$columnName] ?? [])
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
                ->setPrimaryKeyColumns($this->getPrimaryKeyColumnNames(
                    $dbtPrimaryKey,
                    $tableDef->getColumnsNames()
                ))
            ;

            $this->manifestManager->writeTableManifest($tableName, $tableManifestOptions);
        }
    }

    /**
     * @param array<string> $dbtPrimaryKeyColumns
     * @param array<string> $columnNames
     * @return array<string>
     */
    public static function getPrimaryKeyColumnNames(array $dbtPrimaryKeyColumns, array $columnNames): array
    {
        $result = [];
        foreach ($dbtPrimaryKeyColumns as $dbtName) {
            $key = array_search(strtolower($dbtName), array_map('strtolower', $columnNames));
            $result[] = $columnNames[$key];
        }

        return $result;
    }

    /**
     * @param array<string> $sourceTables
     * @return SnowflakeTableDefinition[]
     */
    private function getTables(array $sourceTables): array
    {
        $defs = [];
        $schema = $this->databaseConfig['schema'];
        $missingTables = [];
        foreach ($sourceTables as $tableName) {
            try {
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
            } catch (RuntimeException $e) {
                $missingTables[] = $tableName;
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
                []
            );
        }

        if ($missingTables) {
            throw new UserException(
                sprintf(
                    'Tables "%s" specified in output were not created by the transformation.',
                    implode('", "', $missingTables)
                )
            );
        }

        return $defs;
    }
}
