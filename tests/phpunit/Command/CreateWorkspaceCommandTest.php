<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\Command\CreateWorkspaceCommand;
use DbtTransformation\WorkspacesManagementService;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CreateWorkspaceCommandTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../../data';

    private Command $command;
    private CommandTester $commandTester;
    private WorkspacesManagementService $workspaceManagementService;

    public function setUp(): void
    {
        $application = new Application();
        $application->add(new CreateWorkspaceCommand());
        $this->command = $application->find('app:create-workspace');
        $this->commandTester = new CommandTester($this->command);
        $credentials = $this->getEnvVars();
        $this->workspaceManagementService = new WorkspacesManagementService($credentials['url'], $credentials['token']);

        if ($this->getName(false) === 'testCreateWorkspaceCommand') {
            foreach ($this->validInputsProvider() as $inputProvider) {
                $this->workspaceManagementService->deleteWorkspacesAndConfigurations(
                    sprintf('KBC_DEV_%s', $inputProvider['wsName'])
                );
            }
        }
    }

    /**
     * @dataProvider validInputsProvider
     */
    public function testCreateWorkspaceCommand(string $url, string $token, string $wsName): void
    {
        $this->commandTester->setInputs([$url, $token, $wsName]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode, $output);
        $wsName = sprintf('KBC_DEV_%s', $wsName);
        $this->assertStringContainsString(
            sprintf('Workspace "%s" successfully created.', $wsName),
            $output
        );

        $configurationWorkspaces = $this->workspaceManagementService->getConfigurationWorkspaces($wsName);
        $this->assertNotEmpty($configurationWorkspaces);
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testCreateWorkspaceCommandWithInvalidInputs(
        string $url,
        string $token,
        string $wsName,
        string $expectedError
    ): void {
        $this->commandTester->setInputs([$url, $token, $wsName]);
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
        yield 'valid credentials' => $this->getEnvVars() + ['wsName' => 'TEST'];
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
            'wsName' => 'TEST',
            'expectedError' => 'Authorization failed: wrong credentials',
        ];

        $tokenWithoutRoMapping = getenv('KBC_TOKEN_NO_RO_MAPPING');
        if ($tokenWithoutRoMapping === false) {
            throw new RuntimeException('Missing "KBC_TOKEN_NO_RO_MAPPING" env variable!');
        }

        yield 'token in project without zero-copy mapping feature enabled' => [
            'url' => $envVars['url'],
            'token' => $tokenWithoutRoMapping,
            'wsName' => 'TEST',
            'expectedError' => 'Your project does not have read only storage enabled. Please ask our '
                . 'support for turning this feature on.',
        ];
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
