<?php

declare(strict_types=1);

namespace DbtTransformation\Command;

use DbtTransformation\Component;
use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use Generator;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\ClientException;
use Keboola\StorageApi\Components;
use Keboola\StorageApi\Options\Components\ListComponentConfigurationsOptions;
use Keboola\StorageApi\Options\Components\ListConfigurationWorkspacesOptions;
use Keboola\StorageApi\Workspaces;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class GenerateProfilesAndSourcesCommand extends Command
{

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = 'app:generate-profiles-and-sources';
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultDescription = 'Generates profiles.yml and sources for DBT project';

    private DbtProfilesYamlCreateService $createProfilesFileService;
    private DbtSourceYamlCreateService $createSourceFileService;
    private Client $client;
    private Components $components;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->createProfilesFileService = new DbtProfilesYamlCreateService;
        $this->createSourceFileService = new DbtSourceYamlCreateService;
    }

    public function initClient(string $url, string $token): void
    {
        $this->client = new Client(['url' => $url, 'token' => $token]);
        $this->components = new Components($this->client);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('This command generates profiles.yml with credentials to Keboola Workspaces
        and sources.yml');

        $helper = $this->getHelper('question');
        $questionUrl = new Question('Enter your Keboola Connection URL: ');
        $url = $helper->ask($input, $output, $questionUrl);

        $questionToken = new Question('Enter your Keboola Storage API token: ');
        $token = $helper->ask($input, $output, $questionToken);

        $questionDbtSourceName = new Question('Enter your DBT source name: ');
        $dbtSourceName = $helper->ask($input, $output, $questionDbtSourceName);

        $questionDbEnvVarName = new Question('Enter name of environment variable '
            . 'with DB name (e.g.: DBT_KBC_DEV_DATABASE): ');
        $dbEnvVarName = $helper->ask($input, $output, $questionDbEnvVarName);

        $this->initClient($url, $token);

        try {
            $configurations = $this->getConfigurations();
            $configurationNames = [];
            foreach ($configurations as $configuration) {
                if (str_contains($configuration['name'], 'KBC_DEV_')) {
                    $configurationNames[] = $configuration['name'];

                    $workspace = $this->getWorkspace($configuration['id']);

                    if ($workspace['connection']['backend'] !== 'snowflake') {
                        $output->writeln('Only Snowflake backend is supported at the moment');
                        return Command::FAILURE;
                    }

                    $password = $this->getPassword($configuration['configuration']['parameters']['id']);
                    $output->writeln($this->getEnvVars($configuration['name'], $workspace, $password));
                }
            }

            $tablesData = $this->getTablesData();
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                $output->writeln('Authorization failed: wrong credentials');
            } else {
                $output->writeln($e->getMessage());
            }
            return Command::FAILURE;
        }

        try {
            $this->createProfilesFileService->dumpYaml(
                sprintf('%s/dbt-project/', CloneGitRepositoryCommand::DATA_DIR),
                sprintf('%s/dbt-project/dbt_project.yml', CloneGitRepositoryCommand::DATA_DIR),
                $configurationNames
            );
            $this->createSourceFileService->dumpYaml(
                sprintf('%s/dbt-project/', CloneGitRepositoryCommand::DATA_DIR),
                $dbtSourceName,
                $tablesData,
                $dbEnvVarName
            );
        } catch (UserException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln('Sources and profiles.yml files generated. You can now run command ' .
            'app:run-dbt-command');

        return Command::SUCCESS;
    }

    /**
     * @param array<string, array<string, string>> $workspace
     * @return Generator<string>
     */
    private function getEnvVars(string $name, array $workspace, string $password): Generator
    {
        yield sprintf('export DBT_%s_TYPE=%s', $name, $workspace['connection']['backend']);
        yield sprintf('export DBT_%s_SCHEMA=%s', $name, $workspace['connection']['schema']);
        yield sprintf('export DBT_%s_WAREHOUSE=%s', $name, $workspace['connection']['warehouse']);
        yield sprintf('export DBT_%s_DATABASE=%s', $name, $workspace['connection']['database']);
        yield sprintf(
            'export DBT_%s_HOST=%s',
            $name,
            str_replace(Component::STRING_TO_REMOVE_FROM_HOST, '', $workspace['connection']['host'])
        );
        yield sprintf('export DBT_%s_USER=%s', $name, $workspace['connection']['user']);
        yield sprintf('export DBT_%s_PASSWORD=%s', $name, $password);
    }

    protected function getPassword(int $id): string
    {
        ['password' => $password] = (new Workspaces($this->client))->resetWorkspacePassword($id);

        return $password;
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function getWorkspace(string $id): array
    {
        $listConfigurationWorkspacesOptions = new ListConfigurationWorkspacesOptions();
        $listConfigurationWorkspacesOptions
            ->setComponentId(CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID)
            ->setConfigurationId($id);
        [$workspace] = $this->components->listConfigurationWorkspaces($listConfigurationWorkspacesOptions);

        return $workspace;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConfigurations(): array
    {
        $listComponentConfigurationsOptions = new ListComponentConfigurationsOptions();
        $listComponentConfigurationsOptions
            ->setComponentId(CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID)
            ->setIsDeleted(false);

        return $this->components->listComponentConfigurations($listComponentConfigurationsOptions);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function getTablesData(): array
    {
        $tables = $this->client->listTables();
        $tablesData = [];
        foreach ($tables as $table) {
            $tablesData[(string) $table['bucket']['id']][] = $table;
        }

        return $tablesData;
    }
}
