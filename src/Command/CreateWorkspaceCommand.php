<?php

declare(strict_types=1);

namespace DbtTransformation\Command;

use Keboola\StorageApi\Client;
use Keboola\StorageApi\ClientException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateWorkspaceCommand extends Command
{

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
        $output->writeln('This command guides you to creating Keboola workspace for DBT project');

        $helper = $this->getHelper('question');
        $questionUrl = new Question('Enter your Keboola Connection URL: ');
        $url = $helper->ask($input, $output, $questionUrl);

        $questionToken = new Question('Enter your Keboola Storage API token: ');
        $token = $helper->ask($input, $output, $questionToken);
        $client = new Client(['url' => $url, 'token' => $token]);

        try {
            $projectId = $client->verifyToken()['owner']['id'];
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                $output->writeln('Authorization failed: wrong credentials');
            } else {
                $output->writeln($e->getMessage());
            }
            return Command::FAILURE;
        }

        $output->writeln([
            sprintf('1. Go to URL: %s/admin/projects/%d/transformations-v2/workspaces', $url, $projectId),
            '2. Click on "NEW WORKSPACE" button',
            '3. Select "Snowflake SQL"',
            '4. Enter workspace name (description is optional)',
            '5. Go to detail of your newly created workspace',
            '6. Set input mappings corresponding to your DBT models',
            '7. Click on "Load Data"',
            '8. Note configuration ID (last number in URL) for next step',
            '9. Continue with command "app:generate-profiles-and-sources"',
        ]);

        return Command::SUCCESS;
    }
}
