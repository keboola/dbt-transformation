<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

use Symfony\Component\Yaml\Yaml;

class BigQueryDbtSourcesYaml extends DbtSourcesYaml
{

    /**
     * @param array<string, array{projectId?: mixed, tables: array<int, mixed>}> $tablesData
     * @param array<string, array{period: string, count: int}> $freshness
     */
    public function dumpYaml(
        string $projectPath,
        array $tablesData,
        array $freshness,
    ): void {
        $modelFolderPath = sprintf('%s/models/_sources/', $projectPath);
        $this->createFolderIfNotExist($modelFolderPath);

        foreach ($tablesData as $bucket => $tables) {
            $yaml = [
                'version' => 2,
                'sources' => [
                    [
                        'name' => $bucket,
                        'freshness' => $freshness,
                        'project' => '{{ env_var("DBT_KBC_PROD_PROJECT") }}',
                        'schema' => self::formatBucketNameForBigQuery($bucket),
                        'loaded_at_field' => '"_timestamp"',
                        'tables' => array_map($this->formatTableSources(), $tables['tables']),
                    ],
                ],
            ];

            $this->filesystem->dumpFile(
                sprintf('%s/%s.yml', $modelFolderPath, $bucket),
                Yaml::dump($yaml, 8),
            );
        }
    }

    public static function formatBucketNameForBigQuery(string $bucket): string
    {
        return str_replace(['.', '-'], '_', $bucket);
    }
}
