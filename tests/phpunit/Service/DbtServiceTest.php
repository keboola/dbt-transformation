<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use DbtTransformation\Config;
use DbtTransformation\Configuration\ConfigDefinition;
use DbtTransformation\DwhProvider\DwhProviderFactory;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\DbtSourcesYaml;
use DbtTransformation\Helper\DbtCompileHelper;
use DbtTransformation\Helper\ParseDbtOutputHelper;
use DbtTransformation\Service\DbtService;
use DbtTransformation\Service\GitRepositoryService;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DbtServiceTest extends TestCase
{
    private string $dataDir = __DIR__ . '/../../../data';
    private GitRepositoryService $gitRepositoryService;
    private TestLogger $logger;
    private DbtService $dbtService;
    private DwhProviderFactory $dwhProviderFactory;

    public function setUp(): void
    {
        $createProfilesFileService = new DbtProfilesYaml;
        $createSourceFileService = new DbtSourcesYaml;
        $this->gitRepositoryService = new GitRepositoryService($this->dataDir);
        $this->logger = new TestLogger();

        $this->dbtService = new DbtService($this->getProjectPath());
        $this->dwhProviderFactory = new DwhProviderFactory(
            $createSourceFileService,
            $createProfilesFileService,
            $this->logger
        );
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in($this->dataDir));
    }

    private function getProjectPath(): string
    {
        return sprintf('%s/%s', $this->dataDir, 'dbt-project');
    }

    private function getConfig(string $executeStep): Config
    {
        return new Config([
            'authorization' => [
                'workspace' => [
                    'host' => (string) getenv('SNOWFLAKE_HOST'),
                    'warehouse' => (string) getenv('SNOWFLAKE_WAREHOUSE'),
                    'database' => (string) getenv('SNOWFLAKE_DATABASE'),
                    'schema' => (string) getenv('SNOWFLAKE_SCHEMA'),
                    'user' => (string) getenv('SNOWFLAKE_USER'),
                    'password' => (string) getenv('SNOWFLAKE_PASSWORD'),
                ],
            ],
            'parameters' => [
                'git' => [
                    'repo' => 'https://github.com/keboola/dbt-test-project-public.git',
                ],
                'dbt' => [
                    'executeSteps' => [
                        $executeStep,
                    ],
                ],
            ],
        ], new ConfigDefinition());
    }

    public function testDbtDebug(): void
    {
        $config = $this->getConfig(DbtService::COMMAND_DEBUG);
        $this->gitRepositoryService->clone('https://github.com/keboola/dbt-test-project-public.git');
        $provider = $this->dwhProviderFactory->getProvider($config, $this->getProjectPath());
        $provider->createDbtYamlFiles();

        $output = $this->dbtService->runCommand(DbtService::COMMAND_DEBUG);

        self::assertStringContainsString('profiles.yml file', $output);
        self::assertStringContainsString('dbt_project.yml file', $output);
        self::assertStringContainsString('git', $output);
        self::assertStringContainsString('Connection test:', $output);
    }

    public function testDbtCompile(): void
    {
        $config = $this->getConfig(DbtService::COMMAND_COMPILE);
        $this->gitRepositoryService->clone('https://github.com/keboola/dbt-test-project-public.git');
        $provider = $this->dwhProviderFactory->getProvider($config, $this->getProjectPath());
        $provider->createDbtYamlFiles();
        $output = $this->dbtService->runCommand(DbtService::COMMAND_COMPILE);
        $parsedOutput = iterator_to_array(ParseDbtOutputHelper::getMessagesFromOutput($output));

        self::assertStringContainsString(
            'Partial parse save file not found. Starting full parse.',
            $parsedOutput[1]
        );
        self::assertStringContainsString(
            'Found 2 models, 2 tests',
            $parsedOutput[2]
        );
        self::assertStringContainsString(
            'Concurrency: 4 threads (target=\'kbc_prod\')',
            $parsedOutput[3]
        );
        self::assertStringContainsString('Done.', $parsedOutput[4]);

        $compiledSql = DbtCompileHelper::getCompiledSqlFilesContent($this->getProjectPath() . '/target');

        $keys = array_keys($compiledSql);
        self::assertContains('source_not_null_in.c-test-bucket_test__id_.sql', $keys);
        self::assertContains('source_unique_in.c-test-bucket_test__id_.sql', $keys);
        self::assertContains('fct_model.sql', $keys);
        self::assertContains('stg_model.sql', $keys);

        self::assertStringContainsString(
            'select "id"' . PHP_EOL . 'from "SAPI_9317"."in.c-test-bucket"."test"',
            (string) $compiledSql['source_not_null_in.c-test-bucket_test__id_.sql']
        );
        self::assertStringContainsString('with source as (', (string) $compiledSql['stg_model.sql']);
        self::assertStringContainsString(
            'select * from "SAPI_9317"."in.c-test-bucket"."test"',
            (string) $compiledSql['stg_model.sql']
        );
    }
}
