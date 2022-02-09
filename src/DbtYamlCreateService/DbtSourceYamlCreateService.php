<?php

declare(strict_types=1);

namespace DbtTransformation\DbtYamlCreateService;

use Symfony\Component\Yaml\Yaml;

class DbtSourceYamlCreateService extends DbtYamlCreateService
{
    /**
     * @param string[] $workspace
     * @param array<array<string, string>> $inputTables
     */
    public function dumpYaml(string $projectPath, string $sourceName, array $workspace, array $inputTables): void
    {
        $modelFolderPath = sprintf('%s/models', $projectPath);
        $this->createFolderIfNotExist($modelFolderPath);

        $this->filesystem->dumpFile(
            sprintf('%s/src_%s.yml', $modelFolderPath, $sourceName),
            Yaml::dump([
                'version' => 2,
                'sources' => [
                    [
                        'name' => $sourceName,
                        'database' => $workspace['database'],
                        'schema' => $workspace['schema'],
                        'tables' => array_map(
                            static function ($table) {
                                return [
                                    'name' => $table['destination'],
                                    'quoting' => ['identifier' =>  true],
                                ];
                            },
                            $inputTables
                        ),
                    ],
                ],
            ], 6)
        );
    }
}
