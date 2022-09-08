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
    public function dumpYaml(string $projectPath, array $configurationNames = [], string $type = 'snowflake'): void
    {
        $dbtProjectYamlPath = sprintf('%s/dbt_project.yml', $projectPath);
        if (!$this->filesystem->exists($dbtProjectYamlPath)) {
            throw new UserException('Missing file "dbt_project.yml" in your project root');
        }

        $dbtProjectYaml = Yaml::parseFile($dbtProjectYamlPath);

        $outputs = [];
        if (empty($configurationNames)) {
            $outputs['kbc_prod'] = $this->getOutputDefinition($type, 'KBC_PROD');
        }
        foreach ($configurationNames as $configurationName) {
            $outputs[strtolower($configurationName)] = $this->getOutputDefinition($type, $configurationName);
        }

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

    /**
     * @return array<string, string>
     */
    protected function getOutputDefinition(string $type, string $configurationName): array
    {
        if ($type === 'snowflake') {
            return [
                'type' => sprintf('{{ env_var("DBT_%s_TYPE") }}', $configurationName),
                'user' => sprintf('{{ env_var("DBT_%s_USER") }}', $configurationName),
                'password' => sprintf('{{ env_var("DBT_%s_PASSWORD") }}', $configurationName),
                'schema' => sprintf('{{ env_var("DBT_%s_SCHEMA") }}', $configurationName),
                'warehouse' => sprintf('{{ env_var("DBT_%s_WAREHOUSE") }}', $configurationName),
                'database' => sprintf('{{ env_var("DBT_%s_DATABASE") }}', $configurationName),
                'account' => sprintf('{{ env_var("DBT_%s_ACCOUNT") }}', $configurationName),
            ];
        } elseif ($type === 'bigquery') {
            return [
                'type' => sprintf('{{ env_var("DBT_%s_TYPE") }}', $configurationName),
                'method' => sprintf('{{ env_var("DBT_%s_METHOD") }}', $configurationName),
                'project' => sprintf('{{ env_var("DBT_%s_PROJECT") }}', $configurationName),
                'dataset' => sprintf('{{ env_var("DBT_%s_DATASET") }}', $configurationName),
                'threads' => sprintf('{{ env_var("DBT_%s_THREADS") }}', $configurationName),
                'keyfile' => sprintf('{{ env_var("DBT_%s_KEYFILE") }}', $configurationName),
            ];
        } else {
            return [
                'type' => sprintf('{{ env_var("DBT_%s_TYPE") }}', $configurationName),
                'user' => sprintf('{{ env_var("DBT_%s_USER") }}', $configurationName),
                'password' => sprintf('{{ env_var("DBT_%s_PASSWORD") }}', $configurationName),
                'schema' => sprintf('{{ env_var("DBT_%s_SCHEMA") }}', $configurationName),
                'dbname' => sprintf('{{ env_var("DBT_%s_DATABASE") }}', $configurationName),
                'host' => sprintf('{{ env_var("DBT_%s_HOST") }}', $configurationName),
                'port' => sprintf('{{ env_var("DBT_%s_PORT")|as_number }}', $configurationName),
            ];
        }
    }
}
