<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use RuntimeException;

class RemoteBigQueryProvider extends RemoteSnowflakeProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'bigquery';

    public function setEnvVars(): void
    {
        $workspace = $this->config->getRemoteDwh();

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_METHOD=%s', $workspace['method']));
        putenv(sprintf('DBT_KBC_PROD_PROJECT=%s', $workspace['project']));
        putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['project']));
        putenv(sprintf('DBT_KBC_PROD_DATASET=%s', $workspace['dataset']));
        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $workspace['threads']));
        // create temp file with key
        $tmpKeyFile = tempnam(__DIR__ . '/../../', 'key-');
        if ($tmpKeyFile === false) {
            throw new RuntimeException('Creating temp file with key for BigQuery failed');
        }
        file_put_contents($tmpKeyFile, $workspace['#key_content']);
        putenv(sprintf('DBT_KBC_PROD_KEYFILE=%s', $tmpKeyFile));
    }

    /**
     * @return array<int, string>
     */
    public static function getConnectionParams(): array
    {
        return [
            'type',
            'method',
            'project',
            'dataset',
            'threads',
            '#key_content',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        return [
            'type',
            'method',
            'project',
            'dataset',
            'threads',
            'keyfile',
        ];
    }
}
