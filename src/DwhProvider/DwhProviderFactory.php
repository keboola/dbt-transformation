<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\BigQueryDbtSourcesYaml;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\SnowflakeDbtSourcesYaml;
use Psr\Log\LoggerInterface;
use RuntimeException;

class DwhProviderFactory
{
    public const REMOTE_DWH_ALLOWED_TYPES = [
        RemoteSnowflakeProvider::DWH_PROVIDER_TYPE,
        RemotePostgresProvider::DWH_PROVIDER_TYPE,
        RemoteBigQueryProvider::DWH_PROVIDER_TYPE,
        RemoteMssqlProvider::DWH_PROVIDER_TYPE,
        RemoteRedshiftProvider::DWH_PROVIDER_TYPE,
    ];

    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
    ) {
        $this->logger = $logger;
    }

    public function getProvider(Config $config, string $projectPath): DwhProviderInterface
    {
        if ($config->getEnvKbcComponentId() === 'keboola.dbt-transformation') {
            return new LocalSnowflakeProvider(
                new SnowflakeDbtSourcesYaml(),
                new DbtProfilesYaml(),
                $this->logger,
                $config,
                $projectPath,
            );
        } elseif ($config->getEnvKbcComponentId() === 'keboola.dbt-transformation-local-bigquery') {
            return new LocalBigQueryProvider(
                new BigQueryDbtSourcesYaml(),
                new DbtProfilesYaml(),
                $this->logger,
                $config,
                $projectPath,
            );
        } else {
            $type = $config->getRemoteDwh()['type'];

            $provider = match ($type) {
                RemoteSnowflakeProvider::DWH_PROVIDER_TYPE => RemoteSnowflakeProvider::class,
                RemotePostgresProvider::DWH_PROVIDER_TYPE => RemotePostgresProvider::class,
                RemoteBigQueryProvider::DWH_PROVIDER_TYPE => RemoteBigQueryProvider::class,
                RemoteMssqlProvider::DWH_PROVIDER_TYPE => RemoteMssqlProvider::class,
                RemoteRedshiftProvider::DWH_PROVIDER_TYPE => RemoteRedshiftProvider::class,
                default => throw new RuntimeException(sprintf('Remote DWH type "%s" not supported', $type)),
            };

            return new $provider(new DbtProfilesYaml(), $this->logger, $config, $projectPath);
        }
    }
}
