<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use ColinODell\PsrTestLogger\TestLogger;
use DbtTransformation\Config;
use DbtTransformation\Configuration\ConfigDefinition;
use DbtTransformation\DwhProvider\DwhConnectionTypeEnum;
use DbtTransformation\DwhProvider\DwhProviderFactory;
use DbtTransformation\Helper\DbtCompileHelper;
use DbtTransformation\Helper\ParseDbtOutputHelper;
use DbtTransformation\Service\DbtService;
use DbtTransformation\Service\GitRepositoryService;
use Generator;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Throwable;

class DbtServiceTest extends TestCase
{
    private string $dataDir = __DIR__ . '/../../../data';
    private GitRepositoryService $gitRepositoryService;
    private DbtService $dbtService;
    private DwhProviderFactory $dwhProviderFactory;

    public function setUp(): void
    {
        $this->gitRepositoryService = new GitRepositoryService($this->dataDir);
        $logger = new TestLogger();

        $this->dbtService = new DbtService($this->getProjectPath(), DwhConnectionTypeEnum::LOCAL);
        $this->dwhProviderFactory = new DwhProviderFactory($logger);
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in($this->dataDir));
        putenv('KBC_COMPONENTID=keboola.dbt-transformation');
    }

    private function getProjectPath(): string
    {
        return sprintf('%s/%s', $this->dataDir, 'dbt-project');
    }

    private function getConfig(string $backend, string $executeStep): Config
    {
        return new Config([
            'authorization' => [
                'workspace' => $this->getWorkspaceNode($backend),
            ],
            'parameters' => [
                'git' => [
                    'repo' => 'https://github.com/keboola/dbt-test-project-public.git',
                ],
                'dbt' => [
                    'executeSteps' => [
                        ['step' => $executeStep, 'active' => true],
                    ],
                ],
            ],
        ], new ConfigDefinition());
    }

    /**
     * @dataProvider validDbtOptionsProvider
     */
    public function testValidDbtOptions(string $command, DwhConnectionTypeEnum $dwhConnectionType): void
    {
        $dbtService = new DbtService($this->getProjectPath(), $dwhConnectionType);
        try {
            $dbtService->runCommand($command);
        } catch (UserException $e) {
            $this->fail('Command should not fail with valid options: ' . $e->getMessage());
        } catch (Throwable) {
            // It is expected that command fails without dbt project, but must not fail on invalid options
            $this->expectNotToPerformAssertions();
        }
    }

    /**
     * @dataProvider invalidDbtOptionsProvider
     */
    public function testInvalidDbtOptions(
        string $command,
        DwhConnectionTypeEnum $dwhConnectionType,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $dbtService = new DbtService($this->getProjectPath(), $dwhConnectionType);
        $dbtService->runCommand($command);
    }

    /**
     * @return Generator<string, array{0: string, 1: DwhConnectionTypeEnum}>
     */
    public function validDbtOptionsProvider(): Generator
    {
        yield 'valid option resource-type local' => ['dbt ls --resource-type model', DwhConnectionTypeEnum::LOCAL];
        yield 'valid option models local' => ['dbt run --models my_model', DwhConnectionTypeEnum::LOCAL];
        yield 'valid option resource-type remote' => ['dbt ls --resource-type model', DwhConnectionTypeEnum::REMOTE];
        yield 'valid option models remote' => ['dbt run --models my_model', DwhConnectionTypeEnum::REMOTE];
        yield 'valid option target remote' => ['dbt run --target dev', DwhConnectionTypeEnum::REMOTE];
        yield 'valid option shorthand target remote' => ['dbt run -t dev', DwhConnectionTypeEnum::REMOTE];
    }

    /**
     * @return Generator<string, array{0: string, 1: DwhConnectionTypeEnum, 2: string}>
     */
    public function invalidDbtOptionsProvider(): Generator
    {
        yield 'invalid option profiles-dir local dwh' => [
            'dbt run --profiles-dir dir',
            DwhConnectionTypeEnum::LOCAL,
            'You cannot override option --profiles-dir in your dbt command. Please remove it.',
        ];
        yield 'invalid option log-format local dwh' => [
            'dbt run --log-format json',
            DwhConnectionTypeEnum::LOCAL,
            'You cannot override option --log-format in your dbt command. Please remove it.',
        ];
        yield 'invalid option target local dwh' => [
            'dbt run --target dev',
            DwhConnectionTypeEnum::LOCAL,
            'You cannot override option --target in your dbt command. Please remove it.',
        ];
        yield 'invalid option shorthand target local dwh' => [
            'dbt run -t dev',
            DwhConnectionTypeEnum::LOCAL,
            'You cannot override option -t in your dbt command. Please remove it.',
        ];
        yield 'invalid option profiles-dir remote dwh' => [
            'dbt run --profiles-dir dir',
            DwhConnectionTypeEnum::REMOTE,
            'You cannot override option --profiles-dir in your dbt command. Please remove it.',
        ];
        yield 'invalid option log-format remote dwh' => [
            'dbt run --log-format json',
            DwhConnectionTypeEnum::REMOTE,
            'You cannot override option --log-format in your dbt command. Please remove it.',
        ];
    }


    /**
     * @dataProvider backendProvider
     */
    public function testDbtDebug(string $backend): void
    {
        if ($backend === 'bigquery') {
            putenv('KBC_COMPONENTID=keboola.dbt-transformation-local-bigquery');
        }
        $config = $this->getConfig($backend, DbtService::COMMAND_DEBUG);
        $this->gitRepositoryService->clone('https://github.com/keboola/dbt-test-project-public.git');
        $provider = $this->dwhProviderFactory->getProvider($config, $this->getProjectPath());
        $provider->createDbtYamlFiles();

        $output = $this->dbtService->runCommand(DbtService::COMMAND_DEBUG);

        self::assertStringContainsString('profiles.yml file', $output);
        self::assertStringContainsString('dbt_project.yml file', $output);
        self::assertStringContainsString('git', $output);
        self::assertStringContainsString('Connection test:', $output);
    }

    /**
     * @dataProvider backendProvider
     */
    public function testDbtCompile(string $backend, string $firstSql, string $secondSql): void
    {
        if ($backend === 'bigquery') {
            putenv('KBC_COMPONENTID=keboola.dbt-transformation-local-bigquery');
        }

        $config = $this->getConfig($backend, DbtService::COMMAND_COMPILE);
        $this->gitRepositoryService->clone('https://github.com/keboola/dbt-test-project-public.git');
        $provider = $this->dwhProviderFactory->getProvider($config, $this->getProjectPath());
        $provider->createDbtYamlFiles();
        $output = $this->dbtService->runCommand(DbtService::COMMAND_COMPILE);
        $parsedOutput = iterator_to_array(ParseDbtOutputHelper::getMessagesFromOutput($output));

        $stringsToFind = [
            '/Starting full parse./',
            '/Found 2 models, 2 (data )?tests/',
        ];

        $foundedCount = 0;
        foreach ($parsedOutput as $line) {
            foreach ($stringsToFind as $stringToFind) {
                if (preg_match($stringToFind, $line) === 1) {
                    $foundedCount++;
                };
            }
        }
        if ($foundedCount !== count($stringsToFind)) {
            self::fail('Not all strings were found in output');
        }

        $compiledSql = DbtCompileHelper::getCompiledSqlFilesContent($this->getProjectPath() . '/target');

        $keys = array_keys($compiledSql);
        self::assertContains('source_not_null_in.c-test-bucket_test__id_.sql', $keys);
        self::assertContains('source_unique_in.c-test-bucket_test__id_.sql', $keys);
        self::assertContains('fct_model.sql', $keys);
        self::assertContains('stg_model.sql', $keys);

        self::assertStringContainsString(
            $firstSql,
            (string) $compiledSql['source_not_null_in.c-test-bucket_test__id_.sql'],
        );
        self::assertStringContainsString('with source as (', (string) $compiledSql['stg_model.sql']);
        self::assertStringContainsString(
            $secondSql,
            (string) $compiledSql['stg_model.sql'],
        );
    }

    public function testDbtRunWithVars(): void
    {
        if (getenv('DBT_VERSION') === '1.4.9') {
            $this->markTestSkipped('DBT 1.4 are not supporting debug mode to verify variables');
        }

        $backend = 'snowflake';
        $command = DbtService::COMMAND_RUN . ' --vars \'{"var1": "value1", "var2": "value2"}\' --debug';

        $config = $this->getConfig($backend, $command);
        $this->gitRepositoryService->clone('https://github.com/keboola/dbt-test-project-public.git');
        $provider = $this->dwhProviderFactory->getProvider($config, $this->getProjectPath());
        $provider->createDbtYamlFiles();

        $output = $this->dbtService->runCommand($command);

        self::assertStringContainsString('"vars": "{\'var1\': \'value1\', \'var2\': \'value2\'}', $output);
    }

    /**
     * @return array<string, array<string, string>|string>
     */
    protected function getWorkspaceNode(string $backend): array
    {
        if ($backend === 'snowflake') {
            return [
                'host' => (string) getenv('SNOWFLAKE_HOST'),
                'warehouse' => (string) getenv('SNOWFLAKE_WAREHOUSE'),
                'database' => (string) getenv('SNOWFLAKE_DATABASE'),
                'schema' => (string) getenv('SNOWFLAKE_SCHEMA'),
                'user' => (string) getenv('SNOWFLAKE_USER'),
                'password' => (string) getenv('SNOWFLAKE_PASSWORD'),
            ];
        } else {
            return [
                'schema' => (string) getenv('BQ_SCHEMA'),
                'region' => (string) getenv('BQ_LOCATION'),
                'credentials' => [
                    'type' => (string) getenv('BQ_CREDENTIALS_TYPE'),
                    'project_id' => (string) getenv('BQ_CREDENTIALS_PROJECT_ID'),
                    'private_key_id' => (string) getenv('BQ_CREDENTIALS_PRIVATE_KEY_ID'),
                    'private_key' => (string) getenv('BQ_CREDENTIALS_PRIVATE_KEY'),
                    'client_email' => (string) getenv('BQ_CREDENTIALS_CLIENT_EMAIL'),
                    'client_id' => (string) getenv('BQ_CREDENTIALS_CLIENT_ID'),
                    'auth_uri' => (string) getenv('BQ_CREDENTIALS_AUTH_URI'),
                    'token_uri' => (string) getenv('BQ_CREDENTIALS_TOKEN_URI'),
                    'auth_provider_x509_cert_url' => (string) getenv('BQ_CREDENTIALS_AUTH_PROVIDER_X509_CERT_URL'),
                    'client_x509_cert_url' => (string) getenv('BQ_CREDENTIALS_CLIENT_X509_CERT_URL'),
                ],
            ];
        }
    }

    /**
     * @return Generator<string, array{backend: string, firstSql: string, secondSql: string}>
     */
    public function backendProvider(): Generator
    {
        yield 'Snowflake local backend' => [
            'backend' => 'snowflake',
            'firstSql' => 'select "id"' . PHP_EOL . 'from "SAPI_9317"."in.c-test-bucket"."test"',
            'secondSql' => 'select * from "SAPI_9317"."in.c-test-bucket"."test"',
        ];
        yield 'BigQuery local backend' => [
            'backend' => 'bigquery',
            'firstSql' => 'select "id"' . PHP_EOL . 'from `kbc-euw3-55`.`in_c_test_bucket`.`test`'. PHP_EOL,
            'secondSql' => 'select * from `kbc-euw3-55`.`in_c_test_bucket`.`test`',
        ];
    }
}
