<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use DbtTransformation\Config;
use DbtTransformation\ConfigDefinition\ConfigDefinition;
use DbtTransformation\DwhProvider\DwhProviderFactory;
use DbtTransformation\Helper\ParseDbtOutputHelper;
use DbtTransformation\Service\DbtService;
use DbtTransformation\Service\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\Service\DbtYamlCreateService\DbtSourceYamlCreateService;
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
        $createProfilesFileService = new DbtProfilesYamlCreateService;
        $createSourceFileService = new DbtSourceYamlCreateService;
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

        self::assertSame([
            'Running with dbt=1.2.1',
            'Partial parse save file not found. Starting full parse.',
            'Found 2 models, 2 tests, 0 snapshots, 0 analyses, 267 macros, 0 operations,'
            . ' 0 seed files, 2 sources, 0 exposures, 0 metrics',
            'Concurrency: 4 threads (target=\'kbc_prod\')',
            'Done.',
        ], $parsedOutput);

        //@todo test generated compiled sql files in data/dbt-project/target/dir
    }
}
