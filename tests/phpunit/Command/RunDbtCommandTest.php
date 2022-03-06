<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\CloneRepositoryService;
use DbtTransformation\Command\RunDbtCommand;
use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use DbtTransformation\FunctionalTests\OdbcTestConnection;
use DbtTransformation\Traits\SnowflakeTestQueriesTrait;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RunDbtCommandTest extends TestCase
{
    use SnowflakeTestQueriesTrait;

    protected string $dataDir = __DIR__ . '/../../../data';

    private Command $command;
    private CommandTester $commandTester;

    /**
     * @throws \Keboola\Component\UserException
     * @throws \Keboola\SnowflakeDbAdapter\Exception\SnowflakeDbAdapterException
     */
    public function setUp(): void
    {
        $this->connection = OdbcTestConnection::createConnection();
        $this->removeAllTablesAndViews();

        $application = new Application();
        $application->add(new RunDbtCommand());
        $this->command = $application->find('app:run-dbt-command');
        $this->commandTester = new CommandTester($this->command);

        $this->cloneProjectFromGit();
        $this->generateYamlFiles();
        $this->createTestTableWithSampleData();
    }


    /**
     * @dataProvider validInputsProvider
     * @param array<string, string> $expectedMessages
     */
    public function testCreateWorkspaceCommand(string $models, array $expectedMessages): void
    {
        $this->commandTester->setInputs([$models]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode);
        foreach ($expectedMessages as $expectedMessage) {
            $this->assertStringContainsString($expectedMessage, $output);
        }
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testCreateWorkspaceCommandWithInvalidInputs(string $models, string $expectedError): void
    {
        $this->commandTester->setInputs([$models]);
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
        $schemaName = getenv('SNOWFLAKE_SCHEMA');
        yield 'all models' => [
            'models' => '',
            'expectedMessages' => [
                sprintf('1 of 2 OK created view model %s.stg_model', $schemaName),
                sprintf('2 of 2 OK created view model %s.fct_model', $schemaName),
            ],
        ];

        yield 'one model' => [
            'models' => 'stg_model',
            'expectedMessages' => [
                sprintf('1 of 1 OK created view model %s.stg_model', $schemaName),
            ],
        ];

        yield 'two models' => [
            'models' => 'stg_model fct_model',
            'expectedMessages' => [
                sprintf('1 of 2 OK created view model %s.stg_model', $schemaName),
                sprintf('2 of 2 OK created view model %s.fct_model', $schemaName),
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
            'expectedError' => 'The selection criterion \'non-exist\' does not match any nodes',
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
        $credentials = $this->getSnowflakeCredentials();

        $workspace = [
              'backend' => 'snowflake',
              'host' => $credentials['SNOWFLAKE_HOST'],
              'warehouse' => $credentials['SNOWFLAKE_WAREHOUSE'],
              'database' => $credentials['SNOWFLAKE_DATABASE'],
              'schema' => $credentials['SNOWFLAKE_SCHEMA'],
              'user' => $credentials['SNOWFLAKE_USER'],
              'password' => $credentials['SNOWFLAKE_PASSWORD'],
        ];

        $projectPath = sprintf('%s/dbt-project/', $this->dataDir);
        (new DbtProfilesYamlCreateService())->dumpYaml(
            $projectPath,
            sprintf('%s/dbt-project/dbt_project.yml', $this->dataDir),
            $workspace
        );

        (new DbtSourceYamlCreateService())->dumpYaml(
            $projectPath,
            'my_source',
            $workspace,
            [['destination' => 'test']],
        );
    }

    /**
     * @return array<string, string>
     */
    private function getSnowflakeCredentials(): array
    {
        $credentials['SNOWFLAKE_HOST'] = getenv('SNOWFLAKE_HOST') ?: '';
        $credentials['SNOWFLAKE_WAREHOUSE'] = getenv('SNOWFLAKE_WAREHOUSE') ?: '';
        $credentials['SNOWFLAKE_DATABASE'] = getenv('SNOWFLAKE_DATABASE') ?: '';
        $credentials['SNOWFLAKE_SCHEMA'] = getenv('SNOWFLAKE_SCHEMA') ?: '';
        $credentials['SNOWFLAKE_USER'] = getenv('SNOWFLAKE_USER') ?: '';
        $credentials['SNOWFLAKE_PASSWORD'] = getenv('SNOWFLAKE_PASSWORD') ?: '';

        foreach ($credentials as $key => $value) {
            if ($value === '') {
                throw new RuntimeException(sprintf('Missing env variable "%s"', $key));
            }
        }

        return $credentials;
    }
}
