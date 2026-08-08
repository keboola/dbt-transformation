<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Configuration\NodeDefinition\RemoteDwhNode;

class RemoteSnowflakeProvider extends RemoteProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'snowflake';

    /**
     * @param array<int, string> $configurationNames
     * @throws \Keboola\Component\UserException
     */
    public function createDbtYamlFiles(string $profilesPath, array $configurationNames = []): void
    {
        $this->setEnvVars();

        $workspace = $this->config->getRemoteDwh();

        // For privatelink connections dbt derives the wrong hostname from the account name,
        // so inject the real host into every output (generated and user-merged) after merge.
        $additionalOptions = [];
        if (str_contains($workspace['host'], 'privatelink')) {
            $additionalOptions['host'] = $workspace['host'];
        }

        $this->createProfilesFileService->dumpYaml(
            $this->projectPath,
            $profilesPath,
            $this->getOutputs($configurationNames, $this->getDbtParams(), $this->projectIds),
            $additionalOptions,
        );

        $this->logger->info($this->getConnectionLogMessage());
    }

    public function setEnvVars(): void
    {
        $workspace = $this->config->getRemoteDwh();

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_SCHEMA=%s', $workspace['schema']));
        putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['database']));
        putenv(sprintf('DBT_KBC_PROD_WAREHOUSE=%s', $workspace['warehouse']));
        // Exported for profiles-dir users whose own profiles.yml references {{ env_var("DBT_KBC_PROD_HOST") }};
        // privatelink connections need the real host because dbt derives a wrong one from the account name.
        if (str_contains($workspace['host'], 'privatelink')) {
            putenv(sprintf('DBT_KBC_PROD_HOST=%s', $workspace['host']));
        }
        $account = str_replace(LocalSnowflakeProvider::STRING_TO_REMOVE_FROM_HOST, '', $workspace['host']);
        putenv(sprintf('DBT_KBC_PROD_ACCOUNT=%s', $account));
        putenv(sprintf('DBT_KBC_PROD_USER=%s', $workspace['user']));
        putenv(sprintf('DBT_KBC_PROD_PRIVATE_KEY=%s', $workspace[RemoteDwhNode::NODE_PRIVATE_KEY]));
        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $workspace['threads']));
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        return [
            'type',
            'user',
            'schema',
            'warehouse',
            'database',
            'account',
            'threads',
            'private_key',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getRequiredConnectionParams(): array
    {
        return [
            'schema',
            'database',
            'warehouse',
            'host',
            'user',
            'threads',
        ];
    }

    protected function getConnectionLogMessage(): string
    {
        $dwhConfig = $this->config->getRemoteDwh();
        return sprintf('Remote %s DWH: %s', self::DWH_PROVIDER_TYPE, $dwhConfig['host']);
    }
}
