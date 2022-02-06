<?php

declare(strict_types=1);

namespace DbtTransformation\DbtYamlCreateService;

use Keboola\Component\UserException;
use Symfony\Component\Yaml\Yaml;

class DbtProfileYamlCreateService extends DbtYamlCreateService
{
    /**
     * @param string[] $workspace
     * @throws \Keboola\Component\UserException
     */
    public function dumpYaml(string $projectPath, string $dbtProjectYamlPath, array $workspace): void
    {
        if (!$this->filesystem->exists($dbtProjectYamlPath)) {
            throw new UserException(sprintf('Missing file %s in your project', $dbtProjectYamlPath));
        }
        $dbtProjectYaml = Yaml::parseFile($dbtProjectYamlPath);

        $dbtFolderPath = sprintf('%s/.dbt', $projectPath);
        $this->createFolderIfNotExist($dbtFolderPath);

        $this->filesystem->dumpFile(
            sprintf('%s/profile.yml', $dbtFolderPath),
            Yaml::dump([
                $dbtProjectYaml['profile'] => [
                    'target' => 'dev',
                    'outputs' => ['dev' => $workspace],
                ],
            ], 4)
        );
    }
}
