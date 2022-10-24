<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\DbtSourcesYaml;
use Keboola\StorageApi\Client;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LocalSnowflakeProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'snowflake';
    public const STRING_TO_REMOVE_FROM_HOST = '.snowflakecomputing.com';

    protected DbtSourcesYaml $createSourceFileService;
    protected DbtProfilesYaml $createProfilesFileService;
    protected Config $config;
    protected string $projectPath;
    protected LoggerInterface $logger;

    public function __construct(
        DbtSourcesYaml $createSourceFileService,
        DbtProfilesYaml $createProfilesFileService,
        LoggerInterface $logger,
        Config $config,
        string $projectPath
    ) {
        $this->createProfilesFileService = $createProfilesFileService;
        $this->createSourceFileService = $createSourceFileService;
        $this->logger = $logger;
        $this->config = $config;
        $this->projectPath = $projectPath;
    }

    /**
     * @param array<int, string> $configurationNames
     * @throws \Keboola\Component\UserException
     */
    public function createDbtYamlFiles(array $configurationNames = []): void
    {
        $this->createProfilesFileService->dumpYaml(
            $this->projectPath,
            $this->getOutputs($configurationNames, self::getDbtParams())
        );
        $this->setEnvVars();

        $client = new Client([
            'url' => $this->config->getStorageApiUrl(),
            'token' => $this->config->getStorageApiToken(),
        ]);
        $tables = $client->listTables();
        $tablesData = [];
        foreach ($tables as $table) {
            $tablesData[(string) $table['bucket']['id']][] = $table;
        }

        $this->createSourceFileService->dumpYaml(
            $this->projectPath,
            $tablesData,
            $this->config->getFreshness()
        );
    }

    protected function setEnvVars(): void
    {
        $workspace = $this->config->getAuthorization()['workspace'];
        $workspace['type'] = 'snowflake';

        putenv(sprintf('DBT_KBC_PROD_TYPE=%s', $workspace['type']));
        putenv(sprintf('DBT_KBC_PROD_SCHEMA=%s', $workspace['schema']));
        putenv(sprintf('DBT_KBC_PROD_DATABASE=%s', $workspace['database']));
        putenv(sprintf('DBT_KBC_PROD_WAREHOUSE=%s', $workspace['warehouse']));
        $account = str_replace(self::STRING_TO_REMOVE_FROM_HOST, '', $workspace['host']);
        putenv(sprintf('DBT_KBC_PROD_ACCOUNT=%s', $account));
        putenv(sprintf('DBT_KBC_PROD_USER=%s', $workspace['user']));
        putenv(sprintf('DBT_KBC_PROD_PASSWORD=%s', $workspace['password'] ?? $workspace['#password']));
        putenv(sprintf('DBT_KBC_PROD_THREADS=%s', $this->config->getThreads()));
    }

    /**
     * @return array<int, string>
     */
    public static function getConnectionParams(): array
    {
        return [
            'schema',
            'database',
            'warehouse',
            'host',
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
            'user',
            'password',
            'schema',
            'warehouse',
            'database',
            'account',
            'threads',
        ];
    }

    /**
     * @param array<int, string> $configurationNames
     * @param array<int, string> $dbtParams
     * @return array<string, array<string, string>>
     */
    public static function getOutputs(array $configurationNames, array $dbtParams): array
    {
        $outputs = [];
        if (empty($configurationNames)) {
            $outputs['kbc_prod'] = self::getOutputDefinition('KBC_PROD', $dbtParams);
        }

        foreach ($configurationNames as $configurationName) {
            $outputs[strtolower($configurationName)] = self::getOutputDefinition($configurationName, $dbtParams);
        }

        return $outputs;
    }

    /**
     * @param array<int, string> $dbtParams
     * @return array<string, string>
     */
    protected static function getOutputDefinition(string $configurationName, array $dbtParams): array
    {
        // @todo: tahle metoda se muze zjednodusit a byt pro kazdeho providera specificka
        $values = array_map(function ($item) use ($configurationName) {
            $filter = '';
            if ($item === 'threads' || $item === 'port') {
                $filter = '| as_number';
            }
            if ($item === 'trust_cert') {
                $filter = '| as_bool';
            }
            return sprintf('{{ env_var("DBT_%s_%s")%s }}', $configurationName, strtoupper($item), $filter);
        }, $dbtParams);

        $outputDefinition = array_combine($dbtParams, $values);
        if ($outputDefinition === false) {
            throw new RuntimeException('Failed to get output definition');
        }

        return $outputDefinition;
    }
}
