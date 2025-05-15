<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper\OutputManifest;

use Keboola\Component\Manifest\ManifestManager;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptions;
use Keboola\Component\Manifest\ManifestManager\Options\OutTable\ManifestOptionsSchema;
use Keboola\Datatype\Definition\Bigquery;
use Keboola\Datatype\Definition\Common;
use Keboola\Datatype\Definition\Snowflake;
use Keboola\TableBackendUtils\Table\TableDefinitionInterface;

abstract class OutputManifest implements OutputManifestInterface
{
    private ManifestManager $manifestManager;
    private DbtManifestParser $dbtManifestParser;
    private string $backend;
    private bool $quoteIdentifier;
    private bool $legacyFormat;

    public function __construct(
        ManifestManager $manifestManager,
        DbtManifestParser $dbtManifestParser,
        string $backend,
        bool $legacyFormat = true,
        bool $quoteIdentifier = false,
    ) {
        $this->manifestManager = $manifestManager;
        $this->dbtManifestParser = $dbtManifestParser;
        $this->backend = $backend;
        $this->legacyFormat = $legacyFormat;
        $this->quoteIdentifier = $quoteIdentifier;
    }

    /**
     * @param null[]|array<int, array{
     *      destination: string,
     *      source: string,
     *      primary_key?: array<string>,
     *  }> $configuredOutputTables
     * @throws \JsonException
     */
    public function dump(array $configuredOutputTables): void
    {
        $dbtMetadata = $this->dbtManifestParser->parse();
        $dbtModelNames = array_keys($dbtMetadata);

        if ($configuredOutputTables !== []) {
            /** @var array<int, array{
             *     destination: string,
             *     source: string,
             *     primary_key?: array<string>
             * }> $configuredOutputTables */
            $configuredOutputTablesSources = array_map(static function (array $item) {
                return strtolower($item['source']);
            }, $configuredOutputTables);

            $filteredDbtModelNames = [];
            foreach ($dbtModelNames as $modelName) {
                if (in_array(strtolower($modelName), $configuredOutputTablesSources, true)) {
                    $filteredDbtModelNames[] = $modelName;
                }
            }
            $dbtModelNames = $filteredDbtModelNames;
        }

        $tableStructures = $this->getTables($dbtModelNames);

        foreach ($tableStructures as $tableDef) {
            $this->processTableDefinition($tableDef, $dbtMetadata, $configuredOutputTables);
        }
    }

    /**
     * @param array<string, array{
     *      columns: array<string>,
     *      primary_key: array<string>,
     *      metadata: array<array{key: string, value: string}>,
     *      column_metadata: array<string, array<array{key: string, value: mixed}>>
     *  }> $dbtMetadata
     * @param null[]|array<int, array{
     *     destination: string,
     *     source: string,
     *     primary_key?: array<string>,
     * }> $configuredOutputTables
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    protected function processTableDefinition(
        TableDefinitionInterface $tableDef,
        array $dbtMetadata,
        array $configuredOutputTables,
    ): void {
        $tableName = $tableDef->getTableName();
        $destinationTableName = null;
        $configuredPrimaryKeys = [];
        if ($configuredOutputTables !== []) {
            foreach ($configuredOutputTables as $configuredOutputTable) {
                if (isset($configuredOutputTable['source']) &&
                    strtolower($configuredOutputTable['source']) === strtolower($tableName)) {
                    $destinationTableName = $configuredOutputTable['destination'];
                    $configuredPrimaryKeys = $configuredOutputTable['primary_key'] ?? [];
                    break;
                }
            }
        }

        $realTableName = $this->getRealTableName($tableName);
        $dbtColumnsMetadata = $dbtMetadata[$tableName]['column_metadata'] ?? [];

        if ($configuredPrimaryKeys !== []) {
            $dbtPrimaryKey = $configuredPrimaryKeys;
        } else {
            $dbtPrimaryKey = $dbtMetadata[$tableName]['primary_key'] ?? [];
        }

        $columnsMetadata = $this->getColumnsMetadata($tableDef, $dbtColumnsMetadata);
        $tableMetadata = $this->getTableMetadata($tableName, $realTableName, $dbtMetadata);

        $this->createAndWriteTableManifest(
            $tableDef,
            $realTableName,
            $tableMetadata,
            $columnsMetadata,
            $dbtPrimaryKey,
            $destinationTableName,
        );
    }

    /**
     * @param array<int|string, mixed> $dbtColumnsMetadata
     */
    protected function getColumnsMetadata(TableDefinitionInterface $tableDef, array $dbtColumnsMetadata): object
    {
        $columnsMetadata = (object) [];
        foreach ($tableDef->getColumnsDefinitions() as $column) {
            $columnName = $column->getColumnName();
            /** @var Bigquery|Snowflake $columnDefinition */
            $columnDefinition = $column->getColumnDefinition();
            $columnsMetadata->{$columnName} = array_merge(
                $columnDefinition->toMetadata(),
                (array) ($dbtColumnsMetadata[strtolower($columnName)] ?? []),
            );
        }
        return $columnsMetadata;
    }

    /**
     * @param array<string, array{
     *      columns: array<string>,
     *      primary_key: array<string>,
     *      metadata: array<array{key: string, value: string}>,
     *      column_metadata: array<string, array<array{key: string, value: mixed}>>
     *  }> $dbtMetadata
     * @return array<array{key: string, value: string}>
     */
    protected function getTableMetadata(string $tableName, string $realTableName, array $dbtMetadata): array
    {
        $tableMetadata = $dbtMetadata[$tableName]['metadata'] ?? [];
        $tableMetadata[] = ['key' => 'KBC.name', 'value' => $realTableName];
        $tableMetadata[] = ['key' => Common::KBC_METADATA_KEY_BACKEND, 'value' => $this->backend];
        return $tableMetadata;
    }

    /**
     * @param array<array<string, string>> $tableMetadata
     * @param array<string> $dbtPrimaryKey
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    protected function createAndWriteTableManifest(
        TableDefinitionInterface $tableDef,
        string $realTableName,
        array $tableMetadata,
        object $columnsMetadata,
        array $dbtPrimaryKey,
        ?string $destination = null,
    ): void {
        $tableManifestOptions = new ManifestOptions();
        $primaryKeys = self::getPrimaryKeyColumnNames(
            $dbtPrimaryKey,
            $tableDef->getColumnsNames(),
        );

        $metadataBackend = null;
        $tableMetadataKeyValue = [];
        foreach ($tableMetadata as $metadata) {
            if ($metadata['key'] === 'KBC.datatype.backend') {
                $metadataBackend = $metadata['value'];
            }
            $tableMetadataKeyValue[$metadata['key']] = $metadata['value'];
        }

        $schema = [];
        foreach ($tableDef->getColumnsNames() as $columnName) {
            $columnMetadata = $columnsMetadata->$columnName ?? [];
            $dataTypes = [];
            $metadata = [];
            $isNullable = true;
            $primaryKey = false;
            $description = null;

            foreach ($columnMetadata as $meta) {
                if (str_starts_with($meta['key'], 'KBC.datatype.') && $meta['key'] !== 'KBC.datatype.nullable') {
                    $this->setDataType($meta, $dataTypes, $metadataBackend);
                } else {
                    $this->setMetadata($meta, $metadata, $description, $primaryKey, $isNullable);
                }
            }

            $isPK = in_array($columnName, $primaryKeys);
            $schema[] = new ManifestOptionsSchema(
                $columnName,
                $dataTypes,
                $isPK === true ? false : $isNullable,
                $isPK,
                $description,
                empty($metadata) ? null : $metadata,
            );
        }
        $tableManifestOptions->setSchema($schema);
        $tableManifestOptions->setTableMetadata($tableMetadataKeyValue);
        if ($destination) {
            $tableManifestOptions->setDestination($destination);
        }

        $this->manifestManager->writeTableManifest($realTableName, $tableManifestOptions, $this->legacyFormat);
    }

    /**
     * @param array<string, string> $meta
     * @param array<string, array<string, mixed>> $dataTypes
     */
    private function setDataType(array $meta, array &$dataTypes, ?string $defaultBackend): void
    {
        $key = (string) str_replace('KBC.datatype.', '', $meta['key']);
        if ($key === 'basetype') {
            $backend = 'base';
            $key = 'type';
        } else {
            $backend = $defaultBackend ?? 'base';
        }
        if (in_array($key, ['type', 'default'], true)) {
            $dataTypes['base'][$key] = $meta['value'];
            $dataTypes[$backend][$key] = $meta['value'];
        } elseif ($key === 'length') {
            $dataTypes[$backend][$key] = $meta['value'];
        }
    }

    /**
     * @param array<string, string> $meta
     * @param array<string, string> $metadata
     */
    private function setMetadata(
        array $meta,
        array &$metadata,
        ?string &$description,
        bool &$primaryKey,
        bool &$isNullable,
    ): void {
        if ($meta['key'] === 'KBC.description') {
            $description = $meta['value'];
        } elseif ($meta['key'] === 'KBC.primaryKey') {
            $primaryKey = (bool) $meta['value'];
        } elseif ($meta['key'] === 'KBC.datatype.nullable') {
            $isNullable = (bool) $meta['value'];
        } else {
            $metadata[$meta['key']] = $meta['value'];
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
     * @param array<int, string> $dbtModelNames
     * @return TableDefinitionInterface[]
     */
    abstract protected function getTables(array $dbtModelNames): array;

    protected function getRealTableName(string $tableName): string
    {
        return $this->quoteIdentifier || $this->backend === 'bigquery' ? $tableName : strtoupper($tableName);
    }
}
