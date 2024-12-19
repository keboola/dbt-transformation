<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use DbtTransformation\Config;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use Psr\Log\LoggerInterface;

abstract class RemoteProvider extends DwhProvider implements DwhProviderInterface
{
    protected DbtProfilesYaml $createProfilesFileService;
    protected Config $config;
    protected string $projectPath;
    protected LoggerInterface $logger;
    /** @var array<int, int> */
    protected array $projectIds = [];

    public function __construct(
        DbtProfilesYaml $createProfilesFileService,
        LoggerInterface $logger,
        Config $config,
        string $projectPath,
    ) {
        $this->createProfilesFileService = $createProfilesFileService;
        $this->logger = $logger;
        $this->config = $config;
        $this->projectPath = $projectPath;
    }

    /**
     * @param array<int, string> $configurationNames
     * @throws \Keboola\Component\UserException
     */
    public function createDbtYamlFiles(string $profilesPath, array $configurationNames = []): void
    {
        $this->setEnvVars();

        $this->createProfilesFileService->dumpYaml(
            $this->projectPath,
            $profilesPath,
            $this->getOutputs($configurationNames, $this->getDbtParams(), $this->projectIds),
        );

        $this->logger->info($this->getConnectionLogMessage());
    }

    abstract protected function getConnectionLogMessage(): string;

    abstract protected function setEnvVars(): void;

    /**
     * @return array<int, string>
     */
    abstract public static function getRequiredConnectionParams(): array;

    /**
     * @return array<int, string>
     */
    abstract public static function getDbtParams(): array;

    public function getDwhConnectionType(): DwhConnectionTypeEnum
    {
        return DwhConnectionTypeEnum::REMOTE;
    }
}
