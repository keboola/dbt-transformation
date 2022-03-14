<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\CloneRepositoryService;
use DbtTransformation\Command\GenerateProfilesAndSourcesCommand;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateProfilesAndSourcesCommandTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../../data';

    private Command $command;
    private CommandTester $commandTester;

    /**
     * @throws \Keboola\Component\UserException
     */
    public function setUp(): void
    {
        $application = new Application();
        $application->add(new GenerateProfilesAndSourcesCommand());
        $this->command = $application->find('app:generate-profiles-and-sources');
        $this->commandTester = new CommandTester($this->command);

        $this->cloneProjectFromGit();
    }

    /**
     * @dataProvider validInputsProvider
     */
    public function testCreateWorkspaceCommand(
        string $url,
        string $token,
        string $workspaceConfigurationId,
        string $sourceName
    ): void {
        $this->commandTester->setInputs([$url, $token, $workspaceConfigurationId, $sourceName]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Sources and profiles.yml files generated.', $output);

        $profilesPath = sprintf('%s/dbt-project/.dbt/profiles.yml', $this->dataDir);
        $sourcesPath = sprintf('%s/dbt-project/models/src_%s.yml', $this->dataDir, $sourceName);

        $this->assertFileExists($profilesPath);
        $this->assertStringMatchesFormat(
            $this->getExpectedProfilesContent(),
            file_get_contents($profilesPath) ?: ''
        );

        $this->assertFileExists($sourcesPath);
        $this->assertStringMatchesFormat(
            $this->getExpectedSourcesContent($sourceName),
            file_get_contents($sourcesPath) ?: ''
        );
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testCreateWorkspaceCommandWithInvalidInputs(
        string $url,
        string $token,
        string $workspaceConfigurationId,
        string $sourceName,
        string $expectedError
    ): void {
        $this->commandTester->setInputs([$url, $token, $workspaceConfigurationId, $sourceName]);
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
        [$kbcUrl, $kbcToken, $wsConfId] = $this->getEnvVars();

        yield 'valid credentials' => [
            'url' => $kbcUrl,
            'token' => $kbcToken,
            'workspaceConfigurationId' => $wsConfId,
            'sourceName' => 'my_source',
        ];
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public function invalidInputsProvider(): Generator
    {
        [$kbcUrl, $kbcToken, $wsConfId] = $this->getEnvVars();

        yield 'wrong conf id' => [
            'url' => $kbcUrl,
            'token' => $kbcToken,
            'workspaceConfigurationId' => '1',
            'sourceName' => 'my_source',
            'expectedError' => 'Configuration with ID "1" not found',
        ];

        yield 'invalid token' => [
            'url' => $kbcUrl,
            'token' => $kbcToken . 'invalid',
            'workspaceConfigurationId' => $wsConfId,
            'sourceName' => 'my_source',
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
        dev:
            type: snowflake
            user: %s
            password: %s
            schema: %s
            warehouse: %s
            database: %s
            account: %s
';
    }

    /**
     * @return array<string>
     */
    private function getEnvVars(): array
    {
        $kbcUrl = getenv('KBC_URL');
        $kbcToken = getenv('KBC_TOKEN');
        $wsConfId = getenv('WORKSPACE_CONFIGURATION_ID');

        if ($kbcUrl === false || $kbcToken === false || $wsConfId === false) {
            throw new RuntimeException('Missing KBC env variables!');
        }
        return [$kbcUrl, $kbcToken, $wsConfId];
    }
}
