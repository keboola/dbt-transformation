<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

class RemoteRedshiftProvider extends RemoteProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'redshift';

    public function setEnvVars(): void
    {
        $workspace = $this->config->getRemoteDwh();

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_SCHEMA=%s', $workspace['schema']));
        putenv(sprintf('DBT_KBC_PROD_DBNAME=%s', $workspace['dbname']));
        putenv(sprintf('DBT_KBC_PROD_HOST=%s', $workspace['host']));
        putenv(sprintf('DBT_KBC_PROD_PORT=%s', $workspace['port']));
        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $workspace['threads']));
        putenv(sprintf('DBT_KBC_PROD_USER=%s', $workspace['user']));
        putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace['password'] ?? $workspace['#password']));
    }

    /**
     * @return array<int, string>
     */
    public static function getRequiredConnectionParams(): array
    {
        return [
            'schema',
            'dbname',
            'host',
            'port',
            'threads',
            'user',
            '#password',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        return [
            'type',
            'user',
            'password',
            'schema',
            'dbname',
            'host',
            'port',
            'threads',
        ];
    }

    protected function getConnectionLogMessage(): string
    {
        $dwhConfig = $this->config->getRemoteDwh();
        return sprintf('Remote %s DWH: %s', self::DWH_PROVIDER_TYPE, $dwhConfig['host']);
    }
}
