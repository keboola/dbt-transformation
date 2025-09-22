<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use Keboola\Temp\Temp;
use Psr\Log\LoggerInterface;

class RemoteSnowflakeProvider extends RemoteProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'snowflake';

    private Temp $temp;

    public function __construct(
        DbtProfilesYaml $createProfilesFileService,
        LoggerInterface $logger,
        Config $config,
        string $projectPath,
    ) {
        parent::__construct($createProfilesFileService, $logger, $config, $projectPath);
        $this->temp = new Temp('dbt-snowflake');
    }

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
        // Choose between password or private key
        if (!empty($workspace['#private_key'])) {
            $tmpKeyFile = $this->temp->createFile('snowflake_rsa');
            file_put_contents($tmpKeyFile->getPathname(), $workspace['#private_key']);
            putenv(sprintf('DBT_KBC_PROD_PRIVATE_KEY_PATH=%s', $tmpKeyFile));
            if (!empty($workspace['#private_key_passphrase'])) {
                putenv(sprintf('DBT_KBC_PROD_PRIVATE_KEY_PASSPHRASE=%s', $workspace['#private_key_passphrase']));
            }
        } else {
            putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace['password'] ?? $workspace['#password']));
        }
        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $workspace['threads']));
    }

    /**
     * @return array<int, string>
     */
    public static function getDbtParams(): array
    {
        $params = [
            'type',
            'user',
            'schema',
            'warehouse',
            'database',
            'account',
            'threads',
        ];

        if (getenv('DBT_KBC_PROD_PRIVATE_KEY_PATH') !== false) {
            $params[] = 'private_key_path';
            if (getenv('DBT_KBC_PROD_PRIVATE_KEY_PASSPHRASE') !== false) {
                $params[] = 'private_key_passphrase';
            }
        } else {
            $params[] = 'password';
        }

        return $params;
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

    public function __destruct()
    {
        $this->temp->remove();
    }
}
