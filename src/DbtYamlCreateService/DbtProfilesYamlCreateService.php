<?php

declare(strict_types=1);

namespace DbtTransformation\DbtYamlCreateService;

use DbtTransformation\RemoteDWH\RemoteDWHFactory;
use Keboola\Component\UserException;
use RuntimeException;
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
        $keys = RemoteDWHFactory::getDbtParams($type);

        $values = array_map(function ($item) use ($configurationName) {
            $asNumber = '';
            if ($item === 'threads' || $item === 'port') {
                $asNumber = '| as_number';
            }
            return sprintf('{{ env_var("DBT_%s_%s")%s }}', $configurationName, strtoupper($item), $asNumber);
        }, $keys);

        $outputDefinition = array_combine($keys, $values);
        if ($outputDefinition === false) {
            throw new RuntimeException('Failed to get output definition');
        }

        return $outputDefinition;
    }
}
