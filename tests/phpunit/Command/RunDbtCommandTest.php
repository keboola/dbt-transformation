<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\CloneRepositoryService;
use DbtTransformation\Command\RunDbtCommand;
use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use DbtTransformation\DwhProvider\LocalSnowflakeProvider;
use DbtTransformation\WorkspacesManagementService;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Throwable;

class RunDbtCommandTest extends TestCase
{
    private const TARGET = 'kbc_dev_test';
    protected string $dataDir = __DIR__ . '/../../../data';

    private Command $command;
    private CommandTester $commandTester;
    private WorkspacesManagementService $workspaceManagementService;

    /**
     * @throws \Keboola\Component\UserException
     */
    public function setUp(): void
    {
        $application = new Application();
        $application->add(new RunDbtCommand());
        $this->command = $application->find('app:run-dbt-command');
        $this->commandTester = new CommandTester($this->command);

        $credentials = $this->getEnvVars();
        $this->workspaceManagementService = new WorkspacesManagementService($credentials['url'], $credentials['token']);
        try {
            $this->workspaceManagementService->deleteWorkspacesAndConfigurations(
                GenerateProfilesAndSourcesCommandTest::KBC_DEV_TEST
            );
        } catch (Throwable $e) {
        }
        $workspaceId = $this->workspaceManagementService->createWorkspaceWithConfiguration(
            GenerateProfilesAndSourcesCommandTest::KBC_DEV_TEST
        );
        $this->cloneProjectFromGit();
        $this->generateYamlFiles($workspaceId);
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in($this->dataDir));
    }

    /**
     * @dataProvider validInputsProvider
     * @param array<string, string> $expectedMessages
     */
    public function testRunDbtCommand(string $models, array $expectedMessages): void
    {
        $this->commandTester->setInputs([$models, self::TARGET]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode, $output);
        foreach ($expectedMessages as $expectedMessage) {
            $this->assertStringMatchesFormat($expectedMessage, $output);
        }
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testRunDbtWithInvalidInputs(string $models, string $target, string $expectedError): void
    {
        $this->commandTester->setInputs([$models, $target]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString($expectedError, $output);
    }


    /**
     * @return \Generator<array<string, array<int, string>|string>>
     */
    public function validInputsProvider(): Generator
    {
        yield 'all models' => [
            'models' => '',
            'expectedMessages' => [
                '%a1 of 2 OK created view model %s.stg_model%a',
                '%a2 of 2 OK created view model %s.fct_model%a',
            ],
        ];

        yield 'one model' => [
            'models' => 'stg_model',
            'expectedMessages' => [
                '%a1 of 1 OK created view model %s.stg_model%a',
            ],
        ];

        yield 'two models' => [
            'models' => 'stg_model fct_model',
            'expectedMessages' => [
                '%a1 of 2 OK created view model %s.stg_model%a',
                '%a2 of 2 OK created view model %s.fct_model%a',
            ],
        ];
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public function invalidInputsProvider(): Generator
    {
        yield 'non existing model' => [
            'models' => 'non-exist',
            'target' => self::TARGET,
            'expectedError' => 'The selection criterion \'non-exist\' does not match any nodes',
        ];

        yield 'non existing target' => [
            'models' => '',
            'target' => self::TARGET . 'invalid',
            'expectedError' => 'The profile \'default\' does not have a target named \'' . self::TARGET . 'invalid\'',
        ];
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    private function cloneProjectFromGit(): void
    {
        (new CloneRepositoryService())->clone(
            $this->dataDir,
            'https://github.com/keboola/dbt-test-project-public.git'
        );
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    private function generateYamlFiles(string $workspaceId): void
    {
        $workspace = $this->workspaceManagementService->getWorkspace($workspaceId);
        $workspaceDetails = $workspace->getWorkspaceDetails();
        $host = $workspace->getHost();
        if ($workspaceDetails === null || $host === null) {
            throw new RuntimeException(sprintf(
                'Missing workspace data in sandbox with id "%s"',
                $workspace->getId()
            ));
        }

        putenv(sprintf('DBT_KBC_DEV_TEST_DATABASE=%s', $workspaceDetails['connection']['database']));
        putenv(sprintf('DBT_KBC_DEV_TEST_SCHEMA=%s', $workspaceDetails['connection']['schema']));
        putenv(sprintf('DBT_KBC_DEV_TEST_WAREHOUSE=%s', $workspaceDetails['connection']['warehouse']));
        $account = str_replace(LocalSnowflakeProvider::STRING_TO_REMOVE_FROM_HOST, '', $host);
        putenv(sprintf('DBT_KBC_DEV_TEST_ACCOUNT=%s', $account));
        putenv(sprintf('DBT_KBC_DEV_TEST_TYPE=%s', $workspace->getType()));
        putenv(sprintf('DBT_KBC_DEV_TEST_USER=%s', $workspace->getUser()));
        putenv(sprintf('DBT_KBC_DEV_TEST_PASSWORD=%s', $workspace->getPassword()));

        $projectPath = sprintf('%s/dbt-project/', $this->dataDir);
        (new DbtProfilesYamlCreateService())->dumpYaml(
            $projectPath,
            LocalSnowflakeProvider::getOutputs(
                [GenerateProfilesAndSourcesCommandTest::KBC_DEV_TEST],
                LocalSnowflakeProvider::getDbtParams()
            )
        );

        (new DbtSourceYamlCreateService())->dumpYaml(
            $projectPath,
            ['in.c-test-bucket' => [['name' => 'test', 'primaryKey' => []]]],
            'DBT_KBC_DEV_TEST_DATABASE'
        );
    }

    /**
     * @return array<string, string>
     */
    private function getEnvVars(): array
    {
        $kbcUrl = getenv('KBC_URL');
        $kbcToken = getenv('KBC_TOKEN');

        if ($kbcUrl === false || $kbcToken === false) {
            throw new RuntimeException('Missing KBC env variables!');
        }

        return ['url' => $kbcUrl, 'token' => $kbcToken];
    }
}
