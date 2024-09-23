<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use Keboola\Temp\Temp;
use Psr\Log\LoggerInterface;

class RemoteBigQueryProvider extends RemoteProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'bigquery';

    private Temp $temp;

    public function __construct(
        DbtProfilesYaml $createProfilesFileService,
        LoggerInterface $logger,
        Config $config,
        string $projectPath,
    ) {
        parent::__construct(
            $createProfilesFileService,
            $logger,
            $config,
            $projectPath,
        );

        $this->temp = new Temp('dbt-big-query');
    }

    public function setEnvVars(): void
    {
        $workspace = $this->config->getRemoteDwh();

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_METHOD=%s', $workspace['method']));
        putenv(sprintf('DBT_KBC_PROD_PROJECT=%s', $workspace['project']));
        putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['project']));
        putenv(sprintf('DBT_KBC_PROD_DATASET=%s', $workspace['dataset']));
        if (isset($workspace['location'])) {
            putenv(sprintf('DBT_KBC_PROD_LOCATION=%s', $workspace['location']));
        }
        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $workspace['threads']));
        // create temp file with key
        $tmpKeyFile = $this->temp->createFile('key');
        file_put_contents($tmpKeyFile->getPathname(), $workspace['#key_content']);
        putenv(sprintf('DBT_KBC_PROD_KEYFILE=%s', $tmpKeyFile));
    }

    /**
     * @return array<int, string>
     */
    public static function getRequiredConnectionParams(): array
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
        $dbtParams = [
            'type',
            'method',
            'project',
            'dataset',
            'threads',
            'keyfile',
        ];

        if (getenv('DBT_KBC_PROD_LOCATION') !== false) {
            $dbtParams[] = 'location';
        }

        return $dbtParams;
    }

    protected function getConnectionLogMessage(): string
    {
        $dwhConfig = $this->config->getRemoteDwh();
        return sprintf('Remote %s DWH: %s', self::DWH_PROVIDER_TYPE, $dwhConfig['project']);
    }

    public function __destruct()
    {
        $this->temp->remove();
    }
}
