<?php

declare(strict_types=1);

namespace DbtTransformation\Command;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use Keboola\Component\UserException;
use Keboola\Sandboxes\Api\Client as SandboxesApiClient;
use Keboola\StorageApi\Client as StorageApiClient;
use Keboola\StorageApi\ClientException;
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

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->createProfilesFileService = new DbtProfilesYamlCreateService;
        $this->createSourceFileService = new DbtSourceYamlCreateService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('This command generates profiles.yml with credentials to Keboola Workspace 
        and sources based on your workspace input mapping');

        $helper = $this->getHelper('question');
        $questionUrl = new Question('Enter your Keboola Connection URL: ');
        $url = $helper->ask($input, $output, $questionUrl);

        $questionToken = new Question('Enter your Keboola Storage API token: ');
        $token = $helper->ask($input, $output, $questionToken);

        $questionWorkspaceConfigurationId = new Question('Enter your workspace configuration ID: ');
        $workspaceConfigurationId = $helper->ask($input, $output, $questionWorkspaceConfigurationId);

        $questionDbtSourceName = new Question('Enter your DBT source name: ');
        $dbtSourceName = $helper->ask($input, $output, $questionDbtSourceName);

        $client = new StorageApiClient(['url' => $url, 'token' => $token]);
        $sandboxesApi = new SandboxesApiClient(str_replace('connection', 'sandboxes', $url), $token);

        try {
            $configurationDetail = $client->apiGet(
                sprintf('components/keboola.sandboxes/configs/%d', $workspaceConfigurationId)
            );
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                $output->writeln(sprintf('Configuration with ID "%d" not found', $workspaceConfigurationId));
            } elseif ($e->getCode() === 401) {
                $output->writeln('Authorization failed: wrong credentials');
            } else {
                $output->writeln($e->getMessage());
            }
            return Command::FAILURE;
        }

        $workspaceDetail = $sandboxesApi->get($configurationDetail['configuration']['parameters']['id']);
        if ($workspaceDetail->getType() !== 'snowflake') {
            $output->writeln('Only Snowflake backend is supported at the moment');
            return Command::FAILURE;
        }

        $workspaceDetails = $workspaceDetail->getWorkspaceDetails();
        if ($workspaceDetails === null) {
            $output->writeln('API does not return workspace details');
            return Command::FAILURE;
        }

        $workspace = [
            'host' => $workspaceDetail->getHost(),
            'user' =>$workspaceDetail->getUser(),
            'password' => $workspaceDetail->getPassword(),
        ] + $workspaceDetails['connection'];

        try {
            $this->createProfilesFileService->dumpYaml(
                sprintf('%s/dbt-project/', CloneGitRepositoryCommand::DATA_DIR),
                sprintf('%s/dbt-project/dbt_project.yml', CloneGitRepositoryCommand::DATA_DIR),
                $workspace
            );
        } catch (UserException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $this->createSourceFileService->dumpYaml(
            sprintf('%s/dbt-project/', CloneGitRepositoryCommand::DATA_DIR),
            $dbtSourceName,
            $workspace,
            $configurationDetail['configuration']['storage']['input']['tables']
        );

        $output->writeln('Sources and profiles.yml files generated. You can now run command ' .
            'app:run-dbt-command');

        return Command::SUCCESS;
    }
}
