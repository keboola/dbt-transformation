<?php

declare(strict_types=1);

namespace DbtTransformation\Traits;

use DbtTransformation\Command\CreateWorkspaceCommand;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\Components;
use Keboola\StorageApi\Options\Components\Configuration;
use Keboola\StorageApi\Options\Components\ListConfigurationWorkspacesOptions;
use Keboola\StorageApi\Workspaces;

trait StorageApiClientTrait
{
    private Client $client;

    /**
     * @return array<array<string, mixed>>
     */
    public function getConfigurationWorkspaces(string $configurationId): array
    {
        $components = new Components($this->client);
        $listConfigurationWorkspacesOptions = new ListConfigurationWorkspacesOptions();
        $listConfigurationWorkspacesOptions
            ->setComponentId(CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID)
            ->setConfigurationId($configurationId);

        return $components->listConfigurationWorkspaces($listConfigurationWorkspacesOptions);
    }

    public function deleteWorkspacesAndConfigurations(string $configurationId): void
    {
        $components = new Components($this->client);
        $workspaces = new Workspaces($this->client);
        $configurationWorkspaces = $this->getConfigurationWorkspaces($configurationId);
        foreach ($configurationWorkspaces as $configurationWorkspace) {
            $workspaces->deleteWorkspace($configurationWorkspace['id']);
        }
        $components->deleteConfiguration(CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID, $configurationId);
    }

    public function createWorkspaceWithConfiguration(string $configurationId): void
    {
        $components = new Components($this->client);

        $configuration = new Configuration();
        $configuration->setComponentId(CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID);
        $configuration->setName($configurationId);
        $configuration->setConfigurationId($configurationId);
        $components->addConfiguration($configuration);

        $workspace = $components->createConfigurationWorkspace(
            CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID,
            $configurationId,
            ['backend' => 'snowflake']
        );

        $configuration->setConfiguration(['parameters' => ['id' => $workspace['id']]]);
        $components->updateConfiguration($configuration);
    }
}
