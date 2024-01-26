<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

class RemoteMssqlProvider extends RemoteSnowflakeProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'sqlserver';

    public function setEnvVars(): void
    {
        $workspace = $this->config->getRemoteDwh();

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_DRIVER=%s', 'ODBC Driver 18 for SQL Server'));
        putenv(sprintf('DBT_KBC_PROD_TRUST_CERT=%s', 'True'));
        putenv(sprintf('DBT_KBC_PROD_SERVER=%s', $workspace['server']));
        putenv(sprintf('DBT_KBC_PROD_PORT=%s', $workspace['port']));
        putenv(sprintf('DBT_KBC_PROD_SCHEMA=%s', $workspace['schema']));
        putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['database']));
        putenv(sprintf('DBT_KBC_PROD_USER=%s', $workspace['user']));
        putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace['password'] ?? $workspace['#password']));
        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $workspace['threads']));
    }

    /**
     * @return array<int, string>
     */
    public static function getRequiredConnectionParams(): array
    {
        return [
            'schema',
            'server',
            'database',
            'port',
            'user',
            '#password',
            'threads',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        return [
            'type',
            'schema',
            'driver',
            'server',
            'database',
            'port',
            'user',
            'password',
            'trust_cert',
            'threads',
        ];
    }

    protected function getConnectionLogMessage(): string
    {
        $dwhConfig = $this->config->getRemoteDwh();
        return sprintf('Remote %s DWH: %s', self::DWH_PROVIDER_TYPE, $dwhConfig['server']);
    }
}
