<?php

declare(strict_types=1);

namespace DbtTransformation\Service\DbtYamlCreateService;

use Keboola\Component\UserException;
use Symfony\Component\Yaml\Yaml;

class DbtProfilesYamlCreateService extends DbtYamlCreateService
{
    /**
     * @param array<string, array<string, string>> $outputs
     * @throws UserException
     */
    public function dumpYaml(string $projectPath, array $outputs): void
    {
        $dbtProjectYamlPath = sprintf('%s/dbt_project.yml', $projectPath);
        if (!$this->filesystem->exists($dbtProjectYamlPath)) {
            throw new UserException('Missing file "dbt_project.yml" in your project root');
        }
        $dbtProjectYaml = Yaml::parseFile($dbtProjectYamlPath);
        $this->filesystem->dumpFile(
            sprintf('%s/profiles.yml', $projectPath),
            Yaml::dump([
                'config' => ['send_anonymous_usage_stats' => false],
                $dbtProjectYaml['profile'] => [
                    'target' => 'dev',
                    'outputs' => $outputs,
                ],
            ], 5)
        );
    }
}
