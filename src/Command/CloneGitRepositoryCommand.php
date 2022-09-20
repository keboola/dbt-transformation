<?php

declare(strict_types=1);

namespace DbtTransformation\Command;

use DbtTransformation\Service\GitRepositoryService;
use Keboola\Component\UserException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CloneGitRepositoryCommand extends Command
{

    public const DATA_DIR = __DIR__ . '/../../data';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = 'app:clone-repository';
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultDescription = 'Clone GIT repository with your DBT project';

    private GitRepositoryService $cloneRepositoryService;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->cloneRepositoryService = new GitRepositoryService(self::DATA_DIR);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('This command clones your GIT repository with DBT project for you being able to run 
        DBT project locally against Keboola Workspace');

        $helper = $this->getHelper('question');
        $questionRepositoryUrl = new Question('Enter your GIT repository URL: ');
        $repositoryUrl = $helper->ask($input, $output, $questionRepositoryUrl);

        $questionBranch = new Question('Enter GIT branch (optional): ');
        $branch = $helper->ask($input, $output, $questionBranch);

        $questionUsername = new Question('Enter GitHub username (optional - needed only for private repository): ');
        $username = $helper->ask($input, $output, $questionUsername);

        $questionPassword = new Question('Enter GitHub PAT (optional - needed only for private repository): ');
        $password = $helper->ask($input, $output, $questionPassword);

        try {
            $this->cloneRepositoryService->clone($repositoryUrl, $branch, $username, $password);
        } catch (UserException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln('Project cloned! You can continue with command app:create-workspace');
        return Command::SUCCESS;
    }
}
