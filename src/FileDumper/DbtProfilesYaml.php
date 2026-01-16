<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

use Keboola\Component\UserException;
use Symfony\Component\Yaml\Yaml;

class DbtProfilesYaml extends FilesystemAwareDumper
{
    /**
     * @param array<string, array<string, string|bool>> $outputs
     * @throws UserException
     */
    public function dumpYaml(
        string $projectPath,
        string $profilesPath,
        array $outputs,
        bool $addOcspFailOpen = false,
    ): void {
        $dbtProjectYamlPath = sprintf('%s/dbt_project.yml', $projectPath);
        if (!$this->filesystem->exists($dbtProjectYamlPath)) {
            throw new UserException('Missing file "dbt_project.yml" in your project root');
        }
        $dbtProjectYaml = (array) Yaml::parseFile($dbtProjectYamlPath);
        if (!array_key_exists('profile', $dbtProjectYaml)) {
            throw new UserException('Missing key "profile" in "dbt_project.yml"');
        }

        if ($this->filesystem->exists(sprintf('%s/profiles.yml', $profilesPath))) {
            $profiles = (array) Yaml::parseFile(sprintf('%s/profiles.yml', $profilesPath));
            if (array_key_exists($dbtProjectYaml['profile'], $profiles)
                && is_array($profiles[$dbtProjectYaml['profile']])
                && array_key_exists('outputs', $profiles[$dbtProjectYaml['profile']])
            ) {
                $outputs = array_merge($profiles[$dbtProjectYaml['profile']]['outputs'], $outputs);
            }
        }

        if ($addOcspFailOpen) {
            foreach ($outputs as $outputName => $outputConfig) {
                if (!is_array($outputConfig)) {
                    continue;
                }
                if (!array_key_exists('ocsp_fail_open', $outputConfig)) {
                    $outputConfig['ocsp_fail_open'] = true;
                    $outputs[$outputName] = $outputConfig;
                }
            }
        }

        $this->filesystem->dumpFile(
            sprintf('%s/profiles.yml', $profilesPath),
            Yaml::dump([
                'config' => ['send_anonymous_usage_stats' => false],
                $dbtProjectYaml['profile'] => [
                    'target' => 'dev',
                    'outputs' => $outputs,
                ],
            ], 5),
        );
    }
}
