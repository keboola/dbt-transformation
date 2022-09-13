<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use Psr\Log\LoggerInterface;
use RuntimeException;

class DwhProviderFactory
{
    public const REMOTE_DWH_ALLOWED_TYPES = [
        RemoteSnowflakeProvider::DWH_PROVIDER_TYPE,
        RemotePostgresProvider::DWH_PROVIDER_TYPE,
        RemoteBigQueryProvider::DWH_PROVIDER_TYPE,
    ];

    private DbtSourceYamlCreateService $createSourceFileService;
    private DbtProfilesYamlCreateService $createProfilesFileService;
    private LoggerInterface $logger;

    public function __construct(
        DbtSourceYamlCreateService $createSourceFileService,
        DbtProfilesYamlCreateService $createProfilesFileService,
        LoggerInterface $logger
    ) {
        $this->createProfilesFileService = $createProfilesFileService;
        $this->createSourceFileService = $createSourceFileService;
        $this->logger = $logger;
    }

    public function getProvider(Config $config, string $projectPath): DwhProviderInterface
    {
        if (!$config->hasRemoteDwh()) {
            return new LocalSnowflakeProvider(
                $this->createSourceFileService,
                $this->createProfilesFileService,
                $this->logger,
                $config,
                $projectPath
            );
        }

        $type = $config->getRemoteDwh()['type'];

        switch ($type) {
            case RemoteSnowflakeProvider::DWH_PROVIDER_TYPE:
                return new RemoteSnowflakeProvider(
                    $this->createSourceFileService,
                    $this->createProfilesFileService,
                    $this->logger,
                    $config,
                    $projectPath
                );

            case RemotePostgresProvider::DWH_PROVIDER_TYPE:
                return new RemotePostgresProvider(
                    $this->createSourceFileService,
                    $this->createProfilesFileService,
                    $this->logger,
                    $config,
                    $projectPath
                );

            case RemoteBigQueryProvider::DWH_PROVIDER_TYPE:
                return new RemoteBigQueryProvider(
                    $this->createSourceFileService,
                    $this->createProfilesFileService,
                    $this->logger,
                    $config,
                    $projectPath
                );
        }

        throw new RuntimeException(sprintf('Remote DWH type "%s" not supported', $type));
    }
}
