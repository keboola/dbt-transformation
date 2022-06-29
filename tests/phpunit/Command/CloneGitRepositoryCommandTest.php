<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\Command\CloneGitRepositoryCommand;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CloneGitRepositoryCommandTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../../data';

    private Command $command;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $application = new Application();
        $application->add(new CloneGitRepositoryCommand());
        $this->command = $application->find('app:clone-repository');
        $this->commandTester = new CommandTester($this->command);
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in($this->dataDir));
    }

    /**
     * @dataProvider validInputsProvider
     */
    public function testCloneGitRepositoryCommand(string $url, string $branch, string $username, string $password): void
    {

        $this->commandTester->setInputs([$url, $branch, $username, $password]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Project cloned!', $output);
        $projectPath = sprintf('%s/dbt-project', $this->dataDir);
        $this->assertDirectoryExists($projectPath);
        $this->assertFileExists(sprintf('%s/dbt_project.yml', $projectPath));
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testCloneGitRepositoryCommandWithInvalidInputs(
        string $url,
        string $branch,
        string $username,
        string $password,
        string $expectedError
    ): void {
        $this->commandTester->setInputs([$url, $branch, $username, $password]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString($expectedError, $output);
        $this->assertDirectoryDoesNotExist(sprintf('%s/dbt-project', $this->dataDir));
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public function validInputsProvider(): Generator
    {
        $ghUsername = getenv('GITHUB_USERNAME');
        $ghPassword = getenv('GITHUB_PASSWORD');

        if ($ghUsername === false || $ghPassword === false) {
            throw new RuntimeException('Missing GitHub env variables!');
        }

        yield 'public repository' => [
            'url' => 'https://github.com/keboola/dbt-test-project-public.git',
            'branch' => '',
            'username' => '',
            'password' => '',
        ];

        yield 'private repository' => [
            'url' => 'https://github.com/keboola/dbt-test-project.git',
            'branch' => '',
            'username' => $ghUsername,
            'password' => $ghPassword,
        ];

        yield 'public repository with branch selected' => [
            'url' => 'https://github.com/keboola/dbt-test-project-public.git',
            'branch' => 'main',
            'username' => '',
            'password' => '',
        ];

        yield 'private repository with branch selected' => [
            'url' => 'https://github.com/keboola/dbt-test-project.git',
            'branch' => 'main',
            'username' => $ghUsername,
            'password' => $ghPassword,
        ];
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public function invalidInputsProvider(): Generator
    {
        yield 'non existing repository' => [
            'url' => 'https://github.com/keboola/non-existing-repository.git',
            'branch' => '',
            'username' => '',
            'password' => '',
            'expectedError' => 'Failed to clone your repository: ' .
                'https://github.com/keboola/non-existing-repository.git',
        ];

        yield 'wrong credentials to private repository' => [
            'url' => 'https://github.com/keboola/dbt-test-project.git',
            'branch' => '',
            'username' => 'wrong',
            'password' => 'credentials',
            'expectedError' => 'Failed to clone your repository: ' .
                'https://wrong:credentials@github.com/keboola/dbt-test-project.git',
        ];

        yield 'non existing branch' => [
            'url' => 'https://github.com/keboola/dbt-test-project-public.git',
            'branch' => 'non-existing-branch',
            'username' => '',
            'password' => '',
            'expectedError' => 'Failed to clone your repository: ' .
                'https://github.com/keboola/dbt-test-project-public.git',
        ];
    }
}
