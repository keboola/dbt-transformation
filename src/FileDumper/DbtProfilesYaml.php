<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class DbtProfilesYaml extends FilesystemAwareDumper
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    /**
     * @param array<string, array<string, string>> $outputs
     * @throws UserException
     */
    public function dumpYaml(string $projectPath, string $profilesPath, array $outputs): void
    {
        $dbtProjectYamlPath = sprintf('%s/dbt_project.yml', $projectPath);
        if (!$this->filesystem->exists($dbtProjectYamlPath)) {
            throw new UserException('Missing file "dbt_project.yml" in your project root');
        }
        $dbtProjectYaml = (array) Yaml::parseFile($dbtProjectYamlPath);
        if (!array_key_exists('profile', $dbtProjectYaml)) {
            throw new UserException('Missing key "profile" in "dbt_project.yml"');
        }

        $existingProfilesPath = sprintf('%s/profiles.yml', $profilesPath);
        if ($this->filesystem->exists($existingProfilesPath)) {
            try {
                $profiles = (array) Yaml::parseFile($existingProfilesPath);
                if (array_key_exists($dbtProjectYaml['profile'], $profiles)
                    && is_array($profiles[$dbtProjectYaml['profile']])
                    && array_key_exists('outputs', $profiles[$dbtProjectYaml['profile']])
                ) {
                    $outputs = array_merge($profiles[$dbtProjectYaml['profile']]['outputs'], $outputs);
                }
            } catch (ParseException $e) {
                $this->logger?->warning(sprintf(
                    'Could not parse existing profiles.yml (possibly contains Jinja templating): %s. ' .
                    'Existing outputs will not be merged.',
                    $e->getMessage(),
                ));
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
