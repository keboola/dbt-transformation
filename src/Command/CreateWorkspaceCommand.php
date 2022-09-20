<?php

declare(strict_types=1);

namespace DbtTransformation\Command;

use DbtTransformation\Service\WorkspacesManagementService;
use Dotenv\Dotenv;
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
    private ?string $apiUrl;
    private ?string $apiToken;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $dotenv = Dotenv::createUnsafeMutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
        $this->apiUrl = getenv('SAPI_URL') ?: null;
        $this->apiToken = getenv('SAPI_TOKEN') ?: null;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('This command creates Keboola workspace for DBT project');

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

        $questionWsName = new Question('Enter workspace name (prefix "KBC_DEV_" will be added automatically): ');
        $wsName = $helper->ask($input, $output, $questionWsName);
        $wsName = sprintf('KBC_DEV_%s', strtoupper($wsName));

        $workspaceManagementService = new WorkspacesManagementService($this->apiUrl, $this->apiToken);

        try {
            $tokenInfo = $workspaceManagementService->getTokenInfo();
            if (!in_array('input-mapping-read-only-storage', $tokenInfo['owner']['features'])) {
                $output->writeln('Your project does not have read only storage enabled. Please ask our '
                . 'support for turning this feature on.');
                return Command::FAILURE;
            }
            $workspaceManagementService->createWorkspaceWithConfiguration($wsName);
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
}
