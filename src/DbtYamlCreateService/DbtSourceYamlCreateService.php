<?php

declare(strict_types=1);

namespace DbtTransformation\DbtYamlCreateService;

use Closure;
use Symfony\Component\Yaml\Yaml;

class DbtSourceYamlCreateService extends DbtYamlCreateService
{
    /**
     * @param array<string, array<int, array<string, mixed>>> $tablesData
     */
    public function dumpYaml(
        string $projectPath,
        array $tablesData,
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
                            'database' => sprintf('{{ env_var("%s") }}', $dbEnvVarName),
                            'schema' => $bucket,
                            'loaded_at_field' => '_timestamp',
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
                            'name' => $primaryColumn,
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
