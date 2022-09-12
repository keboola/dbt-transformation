<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use Keboola\StorageApi\Client;
use RuntimeException;

class LocalSnowflakeProvider implements DwhProviderInterface
{
    public const DWH_PROVIDER_TYPE = 'snowflake';
    public const STRING_TO_REMOVE_FROM_HOST = '.snowflakecomputing.com';

    protected DbtSourceYamlCreateService $createSourceFileService;
    protected DbtProfilesYamlCreateService $createProfilesFileService;
    protected Config $config;
    protected string $projectPath;

    public function __construct(
        DbtSourceYamlCreateService $createSourceFileService,
        DbtProfilesYamlCreateService $createProfilesFileService,
        Config $config,
        string $projectPath
    ) {
        $this->createProfilesFileService = $createProfilesFileService;
        $this->createSourceFileService = $createSourceFileService;
        $this->config = $config;
        $this->projectPath = $projectPath;
    }

    public function setEnvVars(): void
    {
        $workspace = $this->config->getAuthorization()['workspace'];
        $workspace['type'] = 'snowflake';

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
        $this->createProfilesFileService->dumpYaml($this->projectPath, $this->getOutputs($configurationNames));
        $this->setEnvVars();

        $client = new Client([
            'url' => $this->config->getStorageApiUrl(),
            'token' => $this->config->getStorageApiToken()
        ]);
        $tables = $client->listTables();
        $tablesData = [];
        foreach ($tables as $table) {
            $tablesData[(string) $table['bucket']['id']][] = $table;
        }

        $this->createSourceFileService->dumpYaml(
            $this->projectPath,
            $tablesData
        );
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
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function getDbtParams(): array
    {
        return [
            'type',
            'user',
            'password',
            'schema',
            'warehouse',
            'database',
            'account',
        ];
    }

    protected function getOutputs(array $configurationNames): array
    {
        $outputs = [];
        if (empty($configurationNames)) {
            $outputs['kbc_prod'] = $this->getOutputDefinition('KBC_PROD');
        }

        foreach ($configurationNames as $configurationName) {
            $outputs[strtolower($configurationName)] = $this->getOutputDefinition($configurationName);
        }

        return $outputs;
    }

    /**
     * @return array<string, string>
     */
    protected function getOutputDefinition(string $configurationName): array
    {
        $keys = RemoteDWHFactory::getDbtParams(self::DWH_PROVIDER_TYPE,);

        $values = array_map(function ($item) use ($configurationName) {
            $asNumber = '';
            if ($item === 'threads' || $item === 'port') {
                $asNumber = '| as_number';
            }
            return sprintf('{{ env_var("DBT_%s_%s")%s }}', $configurationName, strtoupper($item), $asNumber);
        }, $keys);

        $outputDefinition = array_combine($keys, $values);
        if ($outputDefinition === false) {
            throw new RuntimeException('Failed to get output definition');
        }

        return $outputDefinition;
    }
}
