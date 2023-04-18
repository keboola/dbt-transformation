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
     * @return array<array<string, array<int|string, mixed>>>
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
                JSON_THROW_ON_ERROR
            );

            $modelNodes = array_filter($dbtManifest['nodes'], fn ($node) => $node['resource_type'] === 'model');

            foreach ($modelNodes as $tableData) {
                $tableMetadata = [];
                if (isset($tableData['description'])) {
                    $tableMetadata[] = [
                        'key' => 'description',
                        'value' =>$tableData['description'],
                    ];
                }
                if (isset($tableData['meta']['owner'])) {
                    $tableMetadata[] = [
                        'key' => 'meta.owner',
                        'value' =>$tableData['meta']['owner'],
                    ];
                }

                $primaryKey = [];
                $columnsMetadata = [];
                foreach ($tableData['columns'] as $columnName => $values) {
                    if (isset($values['description'])) {
                        $columnsMetadata[$columnName][] = [
                            'key' => 'description',
                            'value' => $values['description'],
                        ];
                    }
                    if (isset($values['data_type'])) {
                        $columnsMetadata[$columnName][] = [
                            'key' => 'dbt.data_type',
                            'value' => $values['data_type'],
                        ];
                    }
                    if (isset($values['meta'])) {
                        if (isset($values['meta']['primary-key'])) {
                            if ($values['meta']['primary-key'] === true) {
                                $primaryKey[] = $columnName;
                            }
                        }

                        $columnsMetadata[$columnName][] = [
                            'key' => 'dbt.meta',
                            'value' => json_encode($values['meta']),
                        ];
                    }
                }

                $result[$tableData['name']] = [
                    'columns' => array_keys($tableData['columns']),
                    'primary_key' => $primaryKey,
                    'metadata' => $tableMetadata,
                    'column_metadata' => $columnsMetadata,
                ];
            }
        }

        return $result;
    }
}
