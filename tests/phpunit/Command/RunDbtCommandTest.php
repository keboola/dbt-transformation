<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\CloneRepositoryService;
use DbtTransformation\Command\CreateWorkspaceCommand;
use DbtTransformation\Command\RunDbtCommand;
use DbtTransformation\Component;
use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use DbtTransformation\Traits\StorageApiClientTrait;
use Generator;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\Components;
use Keboola\StorageApi\Options\Components\ListConfigurationWorkspacesOptions;
use Keboola\StorageApi\Workspaces;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class RunDbtCommandTest extends TestCase
{
    use StorageApiClientTrait;

    private const TARGET = 'kbc_dev_test';
    protected string $dataDir = __DIR__ . '/../../../data';

    private Command $command;
    private CommandTester $commandTester;

    /**
     * @throws \Keboola\Component\UserException
     */
    public function setUp(): void
    {
        $application = new Application();
        $application->add(new RunDbtCommand());
        $this->command = $application->find('app:run-dbt-command');
        $this->commandTester = new CommandTester($this->command);

        $this->client = new Client($this->getEnvVars());
        $this->cloneProjectFromGit();
        $this->createWorkspaceWithConfiguration(GenerateProfilesAndSourcesCommandTest::KBC_DEV_TEST);
        $this->generateYamlFiles();
    }

    public function tearDown(): void
    {
        $this->deleteWorkspacesAndConfigurations(GenerateProfilesAndSourcesCommandTest::KBC_DEV_TEST);

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

        $this->assertEquals(Command::SUCCESS, $exitCode);
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
    private function generateYamlFiles(): void
    {
        $components = new Components($this->client);
        $configuration = $components->getConfiguration(
            CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID,
            GenerateProfilesAndSourcesCommandTest::KBC_DEV_TEST
        );
        $listConfigurationWorkspacesOptions = new ListConfigurationWorkspacesOptions();
        $listConfigurationWorkspacesOptions
            ->setComponentId(CreateWorkspaceCommand::SANDBOXES_COMPONENT_ID)
            ->setConfigurationId($configuration['id']);
        [$workspace] = $components->listConfigurationWorkspaces($listConfigurationWorkspacesOptions);
        ['password' => $password] = (new Workspaces($this->client))
            ->resetWorkspacePassword($configuration['configuration']['parameters']['id']);

        putenv(sprintf('DBT_KBC_DEV_TEST_DATABASE=%s', $workspace['connection']['database']));
        putenv(sprintf('DBT_KBC_DEV_TEST_SCHEMA=%s', $workspace['connection']['schema']));
        putenv(sprintf('DBT_KBC_DEV_TEST_WAREHOUSE=%s', $workspace['connection']['warehouse']));
        $account = str_replace(Component::STRING_TO_REMOVE_FROM_HOST, '', $workspace['connection']['host']);
        putenv(sprintf('DBT_KBC_DEV_TEST_ACCOUNT=%s', $account));
        putenv(sprintf('DBT_KBC_DEV_TEST_TYPE=%s', 'snowflake'));
        putenv(sprintf('DBT_KBC_DEV_TEST_USER=%s', $workspace['connection']['user']));
        putenv(sprintf('DBT_KBC_DEV_TEST_PASSWORD=%s', $password));

        $projectPath = sprintf('%s/dbt-project/', $this->dataDir);
        (new DbtProfilesYamlCreateService())->dumpYaml(
            $projectPath,
            sprintf('%s/dbt-project/dbt_project.yml', $this->dataDir),
            [GenerateProfilesAndSourcesCommandTest::KBC_DEV_TEST]
        );

        (new DbtSourceYamlCreateService())->dumpYaml(
            $projectPath,
            'my_source',
            ['in.c-test' => [['id' => 'test', 'primaryKey' => []]]],
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
