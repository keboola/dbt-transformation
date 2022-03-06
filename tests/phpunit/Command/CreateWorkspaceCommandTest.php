<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\Command\CreateWorkspaceCommand;
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

    public function setUp(): void
    {
        $application = new Application();
        $application->add(new CreateWorkspaceCommand());
        $this->command = $application->find('app:create-workspace');
        $this->commandTester = new CommandTester($this->command);
    }
    /**
     * @dataProvider validInputsProvider
     */
    public function testCreateWorkspaceCommand(string $url, string $token): void
    {
        $this->commandTester->setInputs([$url, $token]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $explodedToken = explode('-', $token);
        $projectId = reset($explodedToken);

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString(
            sprintf('1. Go to URL: %s/admin/projects/%d/transformations-v2/workspaces', $url, $projectId),
            $output
        );
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testCreateWorkspaceCommandWithInvalidInputs(string $url, string $token, string $expectedError): void
    {
        $this->commandTester->setInputs([$url, $token]);
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
        [$kbcUrl, $kbcToken] = $this->getEnvVars();

        yield 'valid credentials' => [
            'url' => $kbcUrl,
            'token' => $kbcToken,
        ];
    }

    /**
     * @return \Generator<array<string, string>>
     */
    public function invalidInputsProvider(): Generator
    {
        [$kbcUrl, $kbcToken] = $this->getEnvVars();

        yield 'invalid token' => [
            'url' => $kbcUrl,
            'token' => $kbcToken . 'invalid',
            'expectedError' => 'Authorization failed: wrong credentials',
        ];
    }

    /**
     * @return array<string>
     */
    private function getEnvVars(): array
    {
        $kbcUrl = getenv('KBC_URL');
        $kbcToken = getenv('KBC_API_TOKEN');

        if ($kbcUrl === false || $kbcToken === false) {
            throw new RuntimeException('Missing KBC env variables!');
        }
        return [$kbcUrl, $kbcToken];
    }
}
