<?php

declare(strict_types=1);

namespace DbtTransformation\DbtYamlCreateService;

use Keboola\Component\UserException;
use Symfony\Component\Yaml\Yaml;

class DbtProfilesYamlCreateService extends DbtYamlCreateService
{
    /**
     * @param array<int, string> $configurationNames
     * @throws \Keboola\Component\UserException
     */
    public function dumpYaml(string $projectPath, string $dbtProjectYamlPath, array $configurationNames = []): void
    {
        if (!$this->filesystem->exists($dbtProjectYamlPath)) {
            throw new UserException('Missing file "dbt_project.yml" in your project root');
        }

        $dbtProjectYaml = Yaml::parseFile($dbtProjectYamlPath);

        $outputs = [];
        if (empty($configurationNames)) {
            $outputs['kbc_prod'] = $this->getOutputDefinition('KBC_PROD');
        }
        foreach ($configurationNames as $configurationName) {
            $outputs[strtolower($configurationName)] = $this->getOutputDefinition($configurationName);
        }

        $this->filesystem->dumpFile(
            sprintf('%s/profiles.yml', $projectPath),
            Yaml::dump([
                $dbtProjectYaml['profile'] => [
                    'target' => 'dev',
                    'outputs' => $outputs,
                ],
            ], 5)
        );
    }

    /**
     * @return array<string, string>
     */
    protected function getOutputDefinition(string $configurationName): array
    {
        return [
            'type' => sprintf('{{ env_var("DBT_%s_TYPE") }}', $configurationName),
            'user' => sprintf('{{ env_var("DBT_%s_USER") }}', $configurationName),
            'password' => sprintf('{{ env_var("DBT_%s_PASSWORD") }}', $configurationName),
            'schema' => sprintf('{{ env_var("DBT_%s_SCHEMA") }}', $configurationName),
            'warehouse' => sprintf('{{ env_var("DBT_%s_WAREHOUSE") }}', $configurationName),
            'database' => sprintf('{{ env_var("DBT_%s_DATABASE") }}', $configurationName),
            'account' => sprintf('{{ env_var("DBT_%s_ACCOUNT") }}', $configurationName),
        ];
    }
}
