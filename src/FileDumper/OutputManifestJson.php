<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

class OutputManifestJson extends FilesystemAwareDumper
{
    private string $outputDir;
    private string $sourceManifestPath;

    public function __construct(string $dataDir, string $projectPath)
    {
        parent::__construct();
        $this->outputDir = $dataDir . '/out/tables';
        $this->sourceManifestPath = $projectPath . '/target/manifest.json';
    }

    public function dumpJson(): void
    {
        if (!file_exists($this->sourceManifestPath)) {
            return;
        }

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
                if (isset($values['meta']['primary-key'])) {
                    $columnsMetadata[$columnName][] = [
                        'key' => 'meta.primary-key',
                        'value' => $values['meta']['primary-key'],
                    ];
                    if ($values['meta']['primary-key'] === true) {
                        $primaryKey[] = $columnName;
                    }
                }
                if (isset($values['data_type'])) {
                    $columnsMetadata[$columnName][] = [
                        'key' => 'data_type',
                        'value' => $values['data_type'],
                    ];
                }
            }

            $manifestData = [
                'source' => $tableData['name'],
                'columns' => array_keys($tableData['columns']),
                'primary_key' => $primaryKey,
                'metadata' => $tableMetadata,
                'column_metadata' => $columnsMetadata,
            ];

            $this->filesystem->dumpFile(
                sprintf('%s/%s.manifest', $this->outputDir, $tableData['name']),
                (string) json_encode(array_filter($manifestData))
            );
        }
    }
}
