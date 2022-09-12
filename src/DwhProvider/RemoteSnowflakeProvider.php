<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

class RemoteSnowflakeProvider extends LocalSnowflakeProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'snowflake';

    public function setEnvVars(): void
    {
        $workspace = $this->config->getRemoteDwh();

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_SCHEMA=%s', $workspace['schema']));
        putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['database']));
        putenv(sprintf('DBT_KBC_PROD_WAREHOUSE=%s', $workspace['warehouse']));
        $account = str_replace(self::STRING_TO_REMOVE_FROM_HOST, '', $workspace['host']);
        putenv(sprintf('DBT_KBC_PROD_ACCOUNT=%s', $account));
        putenv(sprintf('DBT_KBC_PROD_USER=%s', $workspace['user']));
        putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace['password'] ?? $workspace['#password']));
    }

    public function createDbtYamlFiles(array $configurationNames = []): void
    {
        $this->createProfilesFileService->dumpYaml($this->projectPath, $configurationNames);
        $this->setEnvVars();
    }

}
