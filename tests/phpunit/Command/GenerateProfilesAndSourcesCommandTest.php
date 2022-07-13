<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\CloneRepositoryService;
use DbtTransformation\Command\GenerateProfilesAndSourcesCommand;
use DbtTransformation\WorkspacesManagementService;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenerateProfilesAndSourcesCommandTest extends TestCase
{
    public const KBC_DEV_TEST = 'KBC_DEV_TEST';
    private const TESTS_WITH_SETUP = [
        'testGenerateProfilesAndSourcesCommand',
        'testGenerateProfilesAndSourcesCommandWithEnvFlag',
    ];
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
        $application->add(new GenerateProfilesAndSourcesCommand());
        $this->command = $application->find('app:generate-profiles-and-sources');
        $this->commandTester = new CommandTester($this->command);
        $credentials = $this->getEnvVars();
        $this->workspaceManagementService = new WorkspacesManagementService($credentials['url'], $credentials['token']);
        if (in_array($this->getName(false), self::TESTS_WITH_SETUP)) {
            $this->cloneProjectFromGit();
            $this->workspaceManagementService->createWorkspaceWithConfiguration(self::KBC_DEV_TEST);
        }
    }

    public function tearDown(): void
    {
        if (in_array($this->getName(false), self::TESTS_WITH_SETUP)) {
            $this->workspaceManagementService->deleteWorkspacesAndConfigurations(self::KBC_DEV_TEST);
        }

        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in($this->dataDir));
    }

    /**
     * @dataProvider validInputsProvider
     */
    public function testGenerateProfilesAndSourcesCommand(
        string $url,
        string $token,
        string $workspaceName
    ): void {
        $this->commandTester->setInputs([$url, $token, $workspaceName]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode, $output);
        $this->assertStringContainsString('Sources and profiles.yml files generated.', $output);

        [$workspace] = $this->workspaceManagementService->getConfigurationWorkspaces(self::KBC_DEV_TEST);
        $this->assertStringContainsString(
            sprintf('export DBT_%s_SCHEMA=%s', self::KBC_DEV_TEST, $workspace['connection']['schema']),
            $output
        );
        $this->assertStringContainsString(
            sprintf('export DBT_%s_USER=%s', self::KBC_DEV_TEST, $workspace['connection']['user']),
            $output
        );
    }

    /**
     * @dataProvider validInputsProvider
     */
    public function testGenerateProfilesAndSourcesCommandWithEnvFlag(
        string $url,
        string $token
    ): void {
        $this->commandTester->setInputs([$url, $token]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName(), '--env' => null]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode, $output);
        $this->assertStringContainsString('Command executed with --env flag. Only environment variables will ' .
            'be printed without generating profiles and sources', $output);

        [$workspace] = $this->workspaceManagementService->getConfigurationWorkspaces(self::KBC_DEV_TEST);
        $this->assertStringContainsString(
            sprintf('export DBT_%s_SCHEMA=%s', self::KBC_DEV_TEST, $workspace['connection']['schema']),
            $output
        );
        $this->assertStringContainsString(
            sprintf('export DBT_%s_USER=%s', self::KBC_DEV_TEST, $workspace['connection']['user']),
            $output
        );
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testGenerateProfilesAndSourcesCommandWithInvalidInputs(
        string $url,
        string $token,
        string $databaseEnvVarName,
        string $expectedError
    ): void {
        $this->commandTester->setInputs([$url, $token, $databaseEnvVarName]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString($expectedError, $output);
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public function validInputsProvider(): Generator
    {
        yield 'valid credentials' =>
            $this->getEnvVars() +
            [
                'workspaceName' => 'test',
            ];
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public function invalidInputsProvider(): Generator
    {
        $envVars = $this->getEnvVars();

        yield 'invalid token' => [
            'url' => $envVars['url'],
            'token' => $envVars['token'] . 'invalid',
            'workspaceName' => 'test',
            'expectedError' => 'Authorization failed: wrong credentials',
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

    protected function getExpectedSourcesContent(string $sourceName): string
    {
        return 'version: 2
sources:
    -
        name: ' . $sourceName . '
        database: %s
        schema: %s
        loaded_at_field: _timestamp
        tables:
            -
                name: %s
                quoting:
                    database: true
                    schema: true
                    identifier: true
';
    }

    protected function getExpectedProfilesContent(): string
    {
        return 'default:
    target: dev
    outputs:
        kbc_dev_test:
            type: \'{{ env_var("DBT_' . self::KBC_DEV_TEST . '_TYPE") }}\'
            user: \'{{ env_var("DBT_' . self::KBC_DEV_TEST . '_USER") }}\'
            password: \'{{ env_var("DBT_' . self::KBC_DEV_TEST . '_PASSWORD") }}\'
            schema: \'{{ env_var("DBT_' . self::KBC_DEV_TEST . '_SCHEMA") }}\'
            warehouse: \'{{ env_var("DBT_' . self::KBC_DEV_TEST . '_WAREHOUSE") }}\'
            database: \'{{ env_var("DBT_' . self::KBC_DEV_TEST . '_DATABASE") }}\'
            account: \'{{ env_var("DBT_' . self::KBC_DEV_TEST . '_ACCOUNT") }}\'
';
    }

    /**
     * @return array<string>
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
