<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Command;

use DbtTransformation\CloneRepositoryService;
use DbtTransformation\Command\GenerateProfilesAndSourcesCommand;
use DbtTransformation\Traits\StorageApiClientTrait;
use Generator;
use Keboola\StorageApi\Client;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenerateProfilesAndSourcesCommandTest extends TestCase
{
    use StorageApiClientTrait;

    public const KBC_DEV_TEST = 'KBC_DEV_TEST';
    protected string $dataDir = __DIR__ . '/../../../data';

    private Command $command;
    private CommandTester $commandTester;

    /**
     * @throws \Keboola\Component\UserException
     */
    public function setUp(): void
    {
        $this->client = new Client($this->getEnvVars());
        $application = new Application();
        $application->add(new GenerateProfilesAndSourcesCommand());
        $this->command = $application->find('app:generate-profiles-and-sources');
        $this->commandTester = new CommandTester($this->command);

        if ($this->getName(false) === 'testGenerateProfilesAndSourcesCommand') {
            $this->cloneProjectFromGit();
            $this->createWorkspaceWithConfiguration(self::KBC_DEV_TEST);
        }
    }

    public function tearDown(): void
    {
        if ($this->getName(false) === 'testGenerateProfilesAndSourcesCommand') {
            $this->deleteWorkspacesAndConfigurations(self::KBC_DEV_TEST);
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
        string $sourceName,
        string $workspaceName
    ): void {
        $this->commandTester->setInputs([$url, $token, $sourceName, $workspaceName]);
        $exitCode = $this->commandTester->execute(['command' => $this->command->getName()]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Sources and profiles.yml files generated.', $output);

        [$workspace] = $this->getConfigurationWorkspaces(self::KBC_DEV_TEST);
        $this->assertStringContainsString(
            sprintf('export DBT_%s_SCHEMA=%s', self::KBC_DEV_TEST, $workspace['connection']['schema']),
            $output
        );
        $this->assertStringContainsString(
            sprintf('export DBT_%s_USER=%s', self::KBC_DEV_TEST, $workspace['connection']['user']),
            $output
        );

        $profilesPath = sprintf('%s/dbt-project/profiles.yml', $this->dataDir);
        $this->assertFileExists($profilesPath);
        $this->assertStringMatchesFormat(
            $this->getExpectedProfilesContent(),
            file_get_contents($profilesPath) ?: ''
        );

        $sourceFiles = (new Finder())->files()->in(sprintf('%s/dbt-project/models/_sources/', $this->dataDir));
        $this->assertNotEmpty($sourceFiles);
        foreach ($sourceFiles as $sourceFile) {
            $this->assertFileExists($sourceFile->getPathname());
            $this->assertStringMatchesFormat(
                $this->getExpectedSourcesContent($sourceName),
                file_get_contents($sourceFile->getPathname()) ?: ''
            );
        }
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testGenerateProfilesAndSourcesCommandWithInvalidInputs(
        string $url,
        string $token,
        string $sourceName,
        string $databaseEnvVarName,
        string $expectedError
    ): void {
        $this->commandTester->setInputs([$url, $token, $sourceName, $databaseEnvVarName]);
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
                'sourceName' => 'my_source',
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
            'sourceName' => 'my_source',
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
