<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\Command\CreateWorkspaceCommand;
use Keboola\Sandboxes\Api\Client as SandboxesClient;
use Keboola\Sandboxes\Api\Sandbox;
use Keboola\StorageApi\Client as SapiClient;
use Keboola\StorageApi\Components;
use Keboola\StorageApi\Options\Components\Configuration;
use Keboola\StorageApi\Options\Components\ListComponentConfigurationsOptions;
use Keboola\StorageApi\Options\Components\ListConfigurationWorkspacesOptions;
use Keboola\StorageApi\Workspaces;

class WorkspacesManagementService
{
    public const SANDBOXES_COMPONENT_ID = 'keboola.sandboxes';

    private SapiClient $sapiClient;
    private SandboxesClient $sandboxesClient;

    public function __construct(string $url, string $token)
    {
        $this->sapiClient = new SapiClient(['url' => $url, 'token' => $token]);
        $this->sandboxesClient = new SandboxesClient(str_replace('connection', 'sandboxes', $url), $token);
    }

    /**
     * @return array<string, mixed>
     */
    public function getTokenInfo(): array
    {
        return $this->sapiClient->verifyToken();
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfigurations(): array
    {
        $components = new Components($this->sapiClient);
        $listComponentConfigurationsOptions = new ListComponentConfigurationsOptions();
        $listComponentConfigurationsOptions
            ->setComponentId(self::SANDBOXES_COMPONENT_ID)
            ->setIsDeleted(false);

        return $components->listComponentConfigurations($listComponentConfigurationsOptions);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getConfigurationWorkspaces(string $configurationId): array
    {
        $components = new Components($this->sapiClient);
        $listConfigurationWorkspacesOptions = new ListConfigurationWorkspacesOptions();
        $listConfigurationWorkspacesOptions
            ->setComponentId(self::SANDBOXES_COMPONENT_ID)
            ->setConfigurationId($configurationId);

        return $components->listConfigurationWorkspaces($listConfigurationWorkspacesOptions);
    }

    public function deleteWorkspacesAndConfigurations(string $configurationId): void
    {
        $components = new Components($this->sapiClient);
        $workspaces = new Workspaces($this->sapiClient);
        [$configurationWorkspace] = $this->getConfigurationWorkspaces($configurationId);
        $workspaces->deleteWorkspace($configurationWorkspace['id']);
        $configuration = $components->getConfiguration(
            self::SANDBOXES_COMPONENT_ID,
            $configurationId
        );
        $this->sandboxesClient->delete((string) $configuration['configuration']['parameters']['id']);
        $components->deleteConfiguration(self::SANDBOXES_COMPONENT_ID, $configurationId);
    }

    public function createWorkspaceWithConfiguration(string $configurationId): string
    {
        $components = new Components($this->sapiClient);

        $configuration = new Configuration();
        $configuration->setComponentId(self::SANDBOXES_COMPONENT_ID);
        $configuration->setName($configurationId);
        $configuration->setConfigurationId($configurationId);
        $components->addConfiguration($configuration);

        $workspace = $components->createConfigurationWorkspace(
            self::SANDBOXES_COMPONENT_ID,
            $configurationId,
            ['backend' => 'snowflake']
        );

        $sandbox = $this->createSandbox($this->getTokenInfo(), $configurationId, $workspace);
        $sandbox = $this->sandboxesClient->create($sandbox);

        $configuration->setConfiguration(['parameters' => ['id' => $sandbox->getId()]]);
        $components->updateConfiguration($configuration);

        return $sandbox->getId();
    }

    public function getWorkspace(string $id): Sandbox
    {
        return $this->sandboxesClient->get($id);
    }

    /**
     * @param array<string, mixed> $tokenInfo
     * @param array<string, mixed> $workspace
     */
    protected function createSandbox(array $tokenInfo, string $configurationId, array $workspace): Sandbox
    {
        $sandbox = new Sandbox();
        $sandbox
            ->setType('snowflake')
            ->setProjectId((string) $tokenInfo['owner']['id'])
            ->setConfigurationId($configurationId)
            ->setTokenId($tokenInfo['id'])
            ->setPhysicalId($workspace['id'])
            ->setActive(true)
            ->setUser($workspace['connection']['user'])
            ->setPassword($workspace['connection']['password'])
            ->setHost($workspace['connection']['host'])
            ->setShared(true)
            ->setWorkspaceDetails([
                'connection' => [
                    'schema' => $workspace['connection']['schema'],
                    'warehouse' => $workspace['connection']['warehouse'],
                    'database' => $workspace['connection']['database'],
                ],
            ]);

        return $sandbox;
    }
}
