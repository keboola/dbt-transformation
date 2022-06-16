<?php

declare(strict_types=1);

namespace DbtTransformation\Command;

use Keboola\StorageApi\Client;
use Keboola\StorageApi\ClientException;
use Keboola\StorageApi\Components;
use Keboola\StorageApi\Options\Components\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateWorkspaceCommand extends Command
{
    public const SANDBOXES_COMPONENT_ID = 'keboola.sandboxes';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = 'app:create-workspace';
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultDescription = 'Guide to creating Keboola workspace for DBT project';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('This command creates Keboola workspace for DBT project');

        $helper = $this->getHelper('question');
        $questionUrl = new Question('Enter your Keboola Connection URL: ');
        $url = $helper->ask($input, $output, $questionUrl);

        $questionToken = new Question('Enter your Keboola Storage API token: ');
        $token = $helper->ask($input, $output, $questionToken);

        $questionWsName = new Question('Enter workspace name (prefix "KBC_DEV_" will be added automatically): ');
        $wsName = $helper->ask($input, $output, $questionWsName);
        $wsName = sprintf('KBC_DEV_%s', strtoupper($wsName));

        $client = new Client(['url' => $url, 'token' => $token]);

        try {
            if (!in_array('input-mapping-read-only-storage', $client->verifyToken()['owner']['features'])) {
                $output->writeln('Your project does not have read only storage enabled. Please ask our '
                . 'support for turning this feature on.');
                return Command::FAILURE;
            }
            $this->createWorkspace($client, $wsName);
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                $output->writeln('Authorization failed: wrong credentials');
            } else {
                $output->writeln($e->getMessage());
            }
            return Command::FAILURE;
        }

        $output->writeln(sprintf('Workspace "%s" successfully created.', $wsName));

        return Command::SUCCESS;
    }

    protected function createWorkspace(Client $client, string $wsName): void
    {
        $components = new Components($client);
        $configuration = new Configuration();
        $configuration->setComponentId(CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID);
        $configuration->setName($wsName);
        $configuration->setConfigurationId($wsName);
        $components->addConfiguration($configuration);

        $workspace = $components->createConfigurationWorkspace(
            CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID,
            $wsName,
            ['backend' => 'snowflake']
        );

        $configuration->setConfiguration(['parameters' => ['id' => $workspace['id']]]);
        $components->updateConfiguration($configuration);

    }
}
