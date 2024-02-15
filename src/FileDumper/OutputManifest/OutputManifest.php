<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper\OutputManifest;

use Keboola\Component\Manifest\ManifestManager;
use Keboola\Component\Manifest\ManifestManager\Options\OutTableManifestOptions;
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

    public function __construct(
        ManifestManager $manifestManager,
        DbtManifestParser $dbtManifestParser,
        string $backend,
        bool $quoteIdentifier = false,
    ) {
        $this->manifestManager = $manifestManager;
        $this->dbtManifestParser = $dbtManifestParser;
        $this->backend = $backend;
        $this->quoteIdentifier = $quoteIdentifier;
    }

    /**
     * @throws \JsonException
     */
    public function dump(): void
    {
        $dbtMetadata = $this->dbtManifestParser->parse();
        $dbtModelNames = array_keys($dbtMetadata);

        $tableStructures = $this->getTables($dbtModelNames);

        foreach ($tableStructures as $tableDef) {
            $this->processTableDefinition($tableDef, $dbtMetadata);
        }
    }

    /**
     * @param array<string, array{
     *      columns: array<string>,
     *      primary_key: array<string>,
     *      metadata: array<array{key: string, value: string}>,
     *      column_metadata: array<string, array<array{key: string, value: mixed}>>
     *  }> $dbtMetadata
     * @throws \Keboola\Component\Manifest\ManifestManager\Options\OptionsValidationException
     */
    protected function processTableDefinition(TableDefinitionInterface $tableDef, array $dbtMetadata): void
    {
        $tableName = $tableDef->getTableName();
        $realTableName = $this->getRealTableName($tableName);
        $dbtColumnsMetadata = $dbtMetadata[$tableName]['column_metadata'] ?? [];
        $dbtPrimaryKey = $dbtMetadata[$tableName]['primary_key'] ?? [];

        $columnsMetadata = $this->getColumnsMetadata($tableDef, $dbtColumnsMetadata);
        $tableMetadata = $this->getTableMetadata($tableName, $realTableName, $dbtMetadata);

        $this->createAndWriteTableManifest($tableDef, $realTableName, $tableMetadata, $columnsMetadata, $dbtPrimaryKey);
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
    ): void {
        $tableManifestOptions = new OutTableManifestOptions();
        $tableManifestOptions
            ->setMetadata($tableMetadata)
            ->setColumns($tableDef->getColumnsNames())
            ->setColumnMetadata($columnsMetadata)
            ->setPrimaryKeyColumns(self::getPrimaryKeyColumnNames(
                $dbtPrimaryKey,
                $tableDef->getColumnsNames(),
            ));

        $this->manifestManager->writeTableManifest($realTableName, $tableManifestOptions);
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
