<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

use Closure;
use Symfony\Component\Yaml\Yaml;

class DbtSourcesYaml extends FilesystemAwareDumper
{
    /**
     * @param array<string, array<int, array<string, mixed>>> $tablesData
     * @param array<string, array{period: string, count: int}> $freshness
     */
    public function dumpYaml(
        string $projectPath,
        array $tablesData,
        array $freshness,
        string $dbEnvVarName = 'DBT_KBC_PROD_DATABASE'
    ): void {
        $modelFolderPath = sprintf('%s/models/_sources/', $projectPath);
        $this->createFolderIfNotExist($modelFolderPath);

        foreach ($tablesData as $bucket => $tables) {
            $this->filesystem->dumpFile(
                sprintf('%s/%s.yml', $modelFolderPath, $bucket),
                Yaml::dump([
                    'version' => 2,
                    'sources' => [
                        [
                            'name' => $bucket,
                            'freshness' => $freshness,
                            'database' => sprintf('{{ env_var("%s") }}', $dbEnvVarName),
                            'schema' => $bucket,
                            'loaded_at_field' => '"_timestamp"',
                            'tables' => array_map($this->formatTableSources(), $tables),
                        ],
                    ],
                ], 8)
            );
        }
    }

    protected function formatTableSources(): Closure
    {
        return static function ($table) {
            $tables = [
                'name' => $table['name'],
                'quoting' => [
                    'database' => true,
                    'schema' => true,
                    'identifier' => true,
                ],
            ];

            if (!empty($table['primaryKey'])) {
                $tables['columns'] = array_map(
                    static function ($primaryColumn) {
                        return [
                            'name' => sprintf('"%s"', $primaryColumn),
                            'tests' => ['unique', 'not_null'],
                        ];
                    },
                    $table['primaryKey']
                );
            }

            return $tables;
        };
    }
}