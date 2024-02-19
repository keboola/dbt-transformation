<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

use Closure;

abstract class DbtSourcesYaml extends FilesystemAwareDumper
{
    /**
     * @param array<string, array{projectId?: mixed, tables: array<int, mixed>}> $tablesData
     * @param array<string, array{period: string, count: int}> $freshness
     */
    abstract public function dumpYaml(string $projectPath, array $tablesData, array $freshness): void;

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

            if (!empty($table['primaryKey']) && count($table['primaryKey']) === 1) {
                $tables['columns'] = array_map(
                    static function ($primaryColumn) {
                        return [
                            'name' => sprintf('"%s"', $primaryColumn),
                            'tests' => ['unique', 'not_null'],
                        ];
                    },
                    $table['primaryKey'],
                );
            }

            return $tables;
        };
    }
}
