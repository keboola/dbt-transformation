<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Configuration\NodeDefinition\RemoteDwhNode;

class RemoteSnowflakeProvider extends RemoteProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'snowflake';

    public function setEnvVars(): void
    {
        $workspace = $this->config->getRemoteDwh();

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_SCHEMA=%s', $workspace['schema']));
        putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['database']));
        putenv(sprintf('DBT_KBC_PROD_WAREHOUSE=%s', $workspace['warehouse']));
        $account = str_replace(LocalSnowflakeProvider::STRING_TO_REMOVE_FROM_HOST, '', $workspace['host']);
        putenv(sprintf('DBT_KBC_PROD_ACCOUNT=%s', $account));
        putenv(sprintf('DBT_KBC_PROD_USER=%s', $workspace['user']));

        if (isset($workspace[RemoteDwhNode::NODE_PRIVATE_KEY])) {
            putenv(sprintf('DBT_KBC_PROD_PRIVATE_KEY=%s', $workspace[RemoteDwhNode::NODE_PRIVATE_KEY]));
        } else {
            putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace[RemoteDwhNode::NODE_PASSWORD]));
        }

        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $workspace['threads']));
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        $dbtParams = [
            'type',
            'user',
            'schema',
            'warehouse',
            'database',
            'account',
            'threads',
        ];

        if (getenv('DBT_KBC_PROD_PRIVATE_KEY') !== false) {
            $dbtParams[] = 'private_key';
        } else {
            $dbtParams[] = 'password';
        }

        return $dbtParams;
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
