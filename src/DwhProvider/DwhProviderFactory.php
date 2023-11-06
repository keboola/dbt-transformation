<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\DbtSourcesYaml;
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

    private DbtSourcesYaml $createSourceFileService;
    private DbtProfilesYaml $createProfilesFileService;
    private LoggerInterface $logger;

    public function __construct(
        DbtSourcesYaml $createSourceFileService,
        DbtProfilesYaml $createProfilesFileService,
        LoggerInterface $logger,
    ) {
        $this->createProfilesFileService = $createProfilesFileService;
        $this->createSourceFileService = $createSourceFileService;
        $this->logger = $logger;
    }

    public function getProvider(Config $config, string $projectPath): DwhProviderInterface
    {
        if (!$config->hasRemoteDwh()) {
            $provider = LocalSnowflakeProvider::class;
        } else {
            $type = $config->getRemoteDwh()['type'];

            switch ($type) {
                case RemoteSnowflakeProvider::DWH_PROVIDER_TYPE:
                    $provider = RemoteSnowflakeProvider::class;
                    break;

                case RemotePostgresProvider::DWH_PROVIDER_TYPE:
                    $provider = RemotePostgresProvider::class;
                    break;

                case RemoteBigQueryProvider::DWH_PROVIDER_TYPE:
                    $provider = RemoteBigQueryProvider::class;
                    break;

                case RemoteMssqlProvider::DWH_PROVIDER_TYPE:
                    $provider = RemoteMssqlProvider::class;
                    break;

                case RemoteRedshiftProvider::DWH_PROVIDER_TYPE:
                    $provider = RemoteRedshiftProvider::class;
                    break;

                default:
                    throw new RuntimeException(sprintf('Remote DWH type "%s" not supported', $type));
            }
        }

        return new $provider(
            $this->createSourceFileService,
            $this->createProfilesFileService,
            $this->logger,
            $config,
            $projectPath,
        );
    }
}
