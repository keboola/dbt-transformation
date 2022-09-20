<?php

declare(strict_types=1);

namespace DbtTransformation\Command;

use DbtTransformation\DwhProvider\LocalSnowflakeProvider;
use DbtTransformation\Service\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\Service\DbtYamlCreateService\DbtSourceYamlCreateService;
use DbtTransformation\Service\WorkspacesManagementService;
use Dotenv\Dotenv;
use Generator;
use Keboola\Component\UserException;
use Keboola\Sandboxes\Api\Sandbox;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\ClientException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
    private ?string $apiUrl;
    private ?string $apiToken;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->createProfilesFileService = new DbtProfilesYamlCreateService;
        $this->createSourceFileService = new DbtSourceYamlCreateService;
        $dotenv = Dotenv::createUnsafeMutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
        $this->apiUrl = getenv('SAPI_URL') ?: null;
        $this->apiToken = getenv('SAPI_TOKEN') ?: null;
    }

    protected function configure(): void
    {
        $this->addOption('env', null, InputOption::VALUE_NONE, 'Only print environment '
            . ' variables without generating profiles and sources');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $onlyPrintEnv = $input->getOption('env');
        if ($onlyPrintEnv) {
            $output->writeln('Command executed with --env flag. Only environment variables will be printed ' .
                'without generating profiles and sources');
        } else {
            $output->writeln('This command generates profiles.yml with credentials to Keboola Workspaces ' .
                'and sources.yml');
        }

        $helper = $this->getHelper('question');
        if (!$this->apiUrl) {
            $questionUrl = new Question('Enter your Keboola Connection URL '
                . '(e.g. https://connection.keboola.com): ');
            $this->apiUrl = $helper->ask($input, $output, $questionUrl);
        }

        if (!$this->apiToken) {
            $questionToken = new Question('Enter your Keboola Storage API token: ');
            $this->apiToken = $helper->ask($input, $output, $questionToken);
        }

        if (!$onlyPrintEnv) {
            $questionWorkspaceName = new Question('Enter name of workspace you want to use ' .
                '(prefix "KBC_DEV_" will be added automatically): ');
            $workspaceName = $helper->ask($input, $output, $questionWorkspaceName);
        }

        $workspaceManagementService = new WorkspacesManagementService($this->apiUrl, $this->apiToken);

        try {
            $configurations = $workspaceManagementService->getConfigurations();
            $configurationNames = [];
            foreach ($configurations as $configuration) {
                if (str_contains($configuration['name'], 'KBC_DEV_')) {
                    $configurationNames[] = (string) $configuration['name'];

                    $workspaceId = (string) $configuration['configuration']['parameters']['id'];
                    $workspace = $workspaceManagementService->getWorkspace($workspaceId);

                    if ($workspace->getType() !== 'snowflake') {
                        $output->writeln('Only Snowflake backend is supported at the moment');
                        return Command::FAILURE;
                    }

                    $output->writeln($this->getEnvVars($configuration['name'], $workspace));
                }
            }

            if ($onlyPrintEnv) {
                return Command::SUCCESS;
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
                $this->getOutputs($configurationNames)
            );
            $this->createSourceFileService->dumpYaml(
                sprintf('%s/dbt-project/', CloneGitRepositoryCommand::DATA_DIR),
                $tablesData,
                sprintf('DBT_KBC_DEV_%s_DATABASE', strtoupper($workspaceName))
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
     * @return Generator<string>
     */
    private function getEnvVars(string $name, Sandbox $workspace): Generator
    {
        $workspaceDetails = $workspace->getWorkspaceDetails();
        $host = $workspace->getHost();
        if ($workspaceDetails === null || $host === null) {
            throw new RuntimeException(sprintf(
                'Missing workspace data in sandbox with id "%s"',
                $workspace->getId()
            ));
        }

        yield sprintf('export DBT_%s_TYPE=%s', $name, $workspace->getType());
        yield sprintf('export DBT_%s_SCHEMA=%s', $name, $workspaceDetails['connection']['schema']);
        yield sprintf('export DBT_%s_WAREHOUSE=%s', $name, $workspaceDetails['connection']['warehouse']);
        yield sprintf('export DBT_%s_DATABASE=%s', $name, $workspaceDetails['connection']['database']);
        yield sprintf(
            'export DBT_%s_ACCOUNT=%s',
            $name,
            str_replace(LocalSnowflakeProvider::STRING_TO_REMOVE_FROM_HOST, '', $host)
        );
        yield sprintf('export DBT_%s_USER=%s', $name, $workspace->getUser());
        yield sprintf('export DBT_%s_PASSWORD=%s', $name, $workspace->getPassword());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function getTablesData(): array
    {
        $client = new Client(['url' => $this->apiUrl, 'token' => $this->apiToken]);
        $tables = $client->listTables();

        $tablesData = [];
        foreach ($tables as $table) {
            $tablesData[(string) $table['bucket']['id']][] = $table;
        }

        return $tablesData;
    }

    /**
     * @param array<int, string> $configurationNames
     * @return array<string, array<string, string>>
     */
    protected function getOutputs(array $configurationNames): array
    {
        $outputs = [];
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
        $keys = [
            'type',
            'user',
            'password',
            'schema',
            'warehouse',
            'database',
            'account',
        ];

        $values = array_map(function ($item) use ($configurationName) {
            return sprintf('{{ env_var("DBT_%s_%s") }}', $configurationName, strtoupper($item));
        }, $keys);

        return array_combine($keys, $values);
    }
}
