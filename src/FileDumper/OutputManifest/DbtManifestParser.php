<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper\OutputManifest;

use Generator;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;

class DbtManifestParser
{
    public Filesystem $filesystem;
    private string $sourceManifestPath;

    public function __construct(string $projectPath)
    {
        $this->filesystem = new Filesystem();
        $this->sourceManifestPath = $projectPath . '/target/manifest.json';
    }

    /**
     * @return array<string, array{
     *      columns: array<string>,
     *      primary_key: array<string>,
     *      metadata: array<array{key: string, value: string}>,
     *      column_metadata: array<string, array<array{key: string, value: mixed}>>
     *  }>
     * @throws \JsonException
     */
    public function parse(): array
    {
        $result = [];
        if (file_exists($this->sourceManifestPath)) {
            $dbtManifest = (array) json_decode(
                (string) file_get_contents($this->sourceManifestPath),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

            /** @var array<string, array{
             *     name: string,
             *     description?: string,
             *     columns: array<string, array{
             *         description?: string,
             *         data_type?: string,
             *         meta?: array{primary-key?: bool}
             *     }>,
             *     meta?: array{owner?: string}
             * }> $modelNodes */
            $modelNodes = array_filter(
                (array) $dbtManifest['nodes'],
                function ($node) {
                    /** @var array<string, mixed> $node */
                    return $node['resource_type'] === 'model';
                },
            );

            foreach ($modelNodes as $tableData) {
                $tableMetadata = [];
                if (isset($tableData['description'])) {
                    $tableMetadata[] = [
                        'key' => 'KBC.description',
                        'value' => (string) $tableData['description'],
                    ];
                }
                if (isset($tableData['meta']['owner'])) {
                    $tableMetadata[] = [
                        'key' => 'meta.owner',
                        'value' => (string) $tableData['meta']['owner'],
                    ];
                }

                $primaryKey = [];
                $columnsMetadata = [];
                $columns = (array) $tableData['columns'];
                foreach ($columns as $columnName => $values) {
                    $columnNameLC = strtolower((string) $columnName);
                    if (isset($values['description'])) {
                        $columnsMetadata[$columnNameLC][] = [
                            'key' => 'KBC.description',
                            'value' => (string) $values['description'],
                        ];
                    }
                    if (isset($values['data_type'])) {
                        $columnsMetadata[$columnNameLC][] = [
                            'key' => 'dbt.data_type',
                            'value' => (string) $values['data_type'],
                        ];
                    }
                    if (isset($values['meta'])) {
                        if (isset($values['meta']['primary-key'])) {
                            if ($values['meta']['primary-key'] === true) {
                                $primaryKey[] = $columnName;
                            }
                        }

                        $columnsMetadata[$columnNameLC][] = [
                            'key' => 'dbt.meta',
                            'value' => (string) json_encode($values['meta']),
                        ];
                    }
                }

                $tableName = strval($tableData['name']);
                $result[$tableName] = [
                    'columns' => array_keys($columns),
                    'primary_key' => $primaryKey,
                    'metadata' => $tableMetadata,
                    'column_metadata' => $columnsMetadata,
                ];
            }
        }

        return $result;
    }
}
