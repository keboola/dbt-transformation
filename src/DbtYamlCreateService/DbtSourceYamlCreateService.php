<?php

declare(strict_types = 1);

namespace DbtTransformation\DbtYamlCreateService;


use Symfony\Component\Yaml\Yaml;

class DbtSourceYamlCreateService extends DbtYamlCreateService
{
    public function dumpYaml(string $projectPath, array $workspace, array $inputTables): void
    {
        $modelFolderPath = sprintf('%s/models', $projectPath);
        $this->createFolderIfNotExist($modelFolderPath);

        $this->filesystem->dumpFile(
            sprintf('%s/src_%s.yml', $modelFolderPath, $workspace['schema']),
            Yaml::dump([
                'version' => 2,
                'sources' => [
                    [
                        'name' => $workspace['schema'],
                        'database' => $workspace['database'],
                        'schema' => $workspace['schema'],
                        'tables' => array_map(
                            static function($table) { return ['name' => $table['source']]; },
                            $inputTables
                        ),
                    ],
                ],
            ], 5)
        );
    }
}