<?php

declare(strict_types=1);

namespace DbtTransformation\DbtYamlCreateService;

use Keboola\Component\UserException;
use Symfony\Component\Yaml\Yaml;

class DbtProfilesYamlCreateService extends DbtYamlCreateService
{
    private const STRING_TO_REMOVE_FROM_HOST = '.snowflakecomputing.com';

    /**
     * @param string[] $workspace
     * @throws \Keboola\Component\UserException
     */
    public function dumpYaml(string $projectPath, string $dbtProjectYamlPath, array $workspace): void
    {
        if (!$this->filesystem->exists($dbtProjectYamlPath)) {
            throw new UserException('Missing file "dbt_project.yml" in your project root');
        }

        $dbtProjectYaml = Yaml::parseFile($dbtProjectYamlPath);

        $dbtFolderPath = sprintf('%s/.dbt', $projectPath);
        $this->createFolderIfNotExist($dbtFolderPath);

        $workspace['account'] = str_replace(self::STRING_TO_REMOVE_FROM_HOST, '', $workspace['host']);
        unset($workspace['host']);

        $this->filesystem->dumpFile(
            sprintf('%s/profiles.yml', $dbtFolderPath),
            Yaml::dump([
                $dbtProjectYaml['profile'] => [
                    'target' => 'dev',
                    'outputs' => ['dev' => ['type' => 'snowflake'] + $workspace],
                ],
            ], 4)
        );
    }
}
