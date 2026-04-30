<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service\DbtYamlCreateService;

use ColinODell\PsrTestLogger\TestLogger;
use DbtTransformation\Config;
use DbtTransformation\Configuration\ConfigDefinition;
use DbtTransformation\DwhProvider\LocalSnowflakeProvider;
use DbtTransformation\DwhProvider\RemoteBigQueryProvider;
use DbtTransformation\DwhProvider\RemoteSnowflakeProvider;
use DbtTransformation\FileDumper\BigQueryDbtSourcesYaml;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use DbtTransformation\FileDumper\SnowflakeDbtSourcesYaml;
use Generator;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class DbtYamlCreateTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../../../data';
    protected string $providerDataDir = __DIR__ . '/../../data';

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in($this->dataDir));
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYaml(): void
    {
        putenv('DBT_KBC_PROD_PRIVATE_KEY=private_key');

        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            LocalSnowflakeProvider::getOutputs(
                ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK'],
                LocalSnowflakeProvider::getDbtParams(),
            ),
        );

        self::assertFileEquals(
            sprintf('%s/expectedProfiles.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir),
        );

        putenv('DBT_KBC_PROD_PRIVATE_KEY');
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlPassword(): void
    {
        putenv('DBT_KBC_PROD_PASSWORD=password');

        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            LocalSnowflakeProvider::getOutputs(
                ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK'],
                LocalSnowflakeProvider::getDbtParams(),
            ),
        );

        self::assertFileEquals(
            sprintf('%s/expectedProfilesPassword.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir),
        );

        putenv('DBT_KBC_PROD_PASSWORD');
    }

    public function testMergeProfilesYaml(): void
    {
        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        $fs->copy(
            sprintf('%s/profiles.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir),
        );

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            RemoteBigQueryProvider::getOutputs(
                [],
                RemoteBigQueryProvider::getDbtParams(),
            ),
        );

        self::assertFileEquals(
            sprintf('%s/expectedRemoteBigQueryProfilesMerged.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir),
        );
    }

    public function testMergeProfilesYamlAtSpecifiedPath(): void
    {
        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        $fs->copy(
            sprintf('%s/profiles/profiles.yml', $this->providerDataDir),
            sprintf('%s/profiles/profiles.yml', $this->dataDir),
        );

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir . '/profiles',
            RemoteBigQueryProvider::getOutputs(
                [],
                RemoteBigQueryProvider::getDbtParams(),
            ),
        );

        self::assertFileEquals(
            sprintf('%s/profiles/expectedProfiles.yml', $this->providerDataDir),
            sprintf('%s/profiles/profiles.yml', $this->dataDir),
        );
    }

    /**
     * @dataProvider remoteBigQueryProvider
     */
    public function testCreateProfileYamlWithRemoteBigQuery(bool $includeLocation, string $expectedProfilesPath): void
    {
        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        if ($includeLocation) {
            putenv('DBT_KBC_PROD_LOCATION=EU');
        } else {
            putenv('DBT_KBC_PROD_LOCATION');
        }

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            RemoteBigQueryProvider::getOutputs(
                [],
                RemoteBigQueryProvider::getDbtParams(),
            ),
        );

        self::assertFileEquals(
            $expectedProfilesPath,
            sprintf('%s/profiles.yml', $this->dataDir),
        );
    }

    public function remoteBigQueryProvider(): Generator
    {
        yield 'without location' => [
            'includeLocation' => false,
            'expectedProfilesPath' => sprintf('%s/expectedRemoteBigQueryProfiles.yml', $this->providerDataDir),
        ];

        yield 'with location' => [
            'includeLocation' => true,
            'expectedProfilesPath' => sprintf(
                '%s/expectedRemoteBigQueryProfilesWithLocation.yml',
                $this->providerDataDir,
            ),
        ];
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlWithRemoteSnowflakeAddsHost(): void
    {
        putenv('DBT_KBC_PROD_PRIVATE_KEY=private_key');

        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            RemoteSnowflakeProvider::getOutputs(
                [],
                RemoteSnowflakeProvider::getDbtParams(),
            ),
            [
                'host' => 'test.privatelink.snowflakecomputing.com',
            ],
        );

        /** @var array<string, array<string, array<string, array<string, mixed>>>> $result */
        $result = Yaml::parseFile(sprintf('%s/profiles.yml', $this->dataDir));

        self::assertSame(
            'test.privatelink.snowflakecomputing.com',
            $result['default']['outputs']['kbc_prod']['host'],
        );
        self::assertArrayNotHasKey('insecure_mode', $result['default']['outputs']['kbc_prod']);

        putenv('DBT_KBC_PROD_PRIVATE_KEY');
    }

    public function testMergedProfilesGetAdditionalHost(): void
    {
        putenv('DBT_KBC_PROD_PRIVATE_KEY=private_key');

        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );
        $fs->copy(
            sprintf('%s/profiles.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir),
        );

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            RemoteSnowflakeProvider::getOutputs(
                [],
                RemoteSnowflakeProvider::getDbtParams(),
            ),
            [
                'host' => 'test.privatelink.snowflakecomputing.com',
            ],
        );

        /** @var array<string, array<string, array<string, array<string, mixed>>>> $result */
        $result = Yaml::parseFile(sprintf('%s/profiles.yml', $this->dataDir));

        // Merged output (from existing profiles.yml) gets host
        self::assertSame(
            'test.privatelink.snowflakecomputing.com',
            $result['default']['outputs']['prod']['host'],
        );
        // Generated output gets host
        self::assertSame(
            'test.privatelink.snowflakecomputing.com',
            $result['default']['outputs']['kbc_prod']['host'],
        );

        putenv('DBT_KBC_PROD_PRIVATE_KEY');
    }

    /**
     * Exercises the provider decision logic end-to-end for a privatelink connection:
     *  - the host is injected into the component-generated profiles.yml (non-profiles-dir users), and
     *  - DBT_KBC_PROD_HOST is exported so profiles-dir users' own profiles.yml can resolve it.
     *
     * @throws \Keboola\Component\UserException
     */
    public function testRemoteSnowflakeProviderInjectsHostForPrivatelink(): void
    {
        $host = 'my_account.privatelink.snowflakecomputing.com';
        $this->createRemoteSnowflakeProvider($host)->createDbtYamlFiles($this->dataDir);

        /** @var array<string, array<string, array<string, array<string, mixed>>>> $result */
        $result = Yaml::parseFile(sprintf('%s/profiles.yml', $this->dataDir));

        self::assertSame($host, $result['default']['outputs']['kbc_prod']['host']);
        self::assertSame($host, getenv('DBT_KBC_PROD_HOST'));

        $this->cleanRemoteSnowflakeEnvVars();
    }

    /**
     * Exercises the provider decision logic end-to-end for a regular connection: the host is neither
     * injected into the generated profiles.yml nor exported, so dbt derives it from the account as before.
     *
     * @throws \Keboola\Component\UserException
     */
    public function testRemoteSnowflakeProviderDoesNotInjectHostForRegularConnection(): void
    {
        putenv('DBT_KBC_PROD_HOST'); // ensure no leakage from a previous test

        $host = 'my_account.snowflakecomputing.com';
        $this->createRemoteSnowflakeProvider($host)->createDbtYamlFiles($this->dataDir);

        /** @var array<string, array<string, array<string, array<string, mixed>>>> $result */
        $result = Yaml::parseFile(sprintf('%s/profiles.yml', $this->dataDir));

        self::assertArrayNotHasKey('host', $result['default']['outputs']['kbc_prod']);
        self::assertFalse(getenv('DBT_KBC_PROD_HOST'));

        $this->cleanRemoteSnowflakeEnvVars();
    }

    private function createRemoteSnowflakeProvider(string $host): RemoteSnowflakeProvider
    {
        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        $config = new Config([
            'parameters' => [
                'git' => ['repo' => 'https://github.com/keboola/dbt-test-project-public.git'],
                'dbt' => ['executeSteps' => [['step' => 'dbt run', 'active' => true]]],
                'remoteDwh' => [
                    'type' => 'snowflake',
                    'host' => $host,
                    'warehouse' => 'warehouse',
                    'database' => 'database',
                    'schema' => 'schema',
                    'user' => 'user',
                    '#privateKey' => 'private_key',
                ],
            ],
        ], new ConfigDefinition());

        return new RemoteSnowflakeProvider(
            new DbtProfilesYaml(),
            new TestLogger(),
            $config,
            $this->dataDir,
        );
    }

    private function cleanRemoteSnowflakeEnvVars(): void
    {
        $names = ['TYPE', 'SCHEMA', 'DATABASE', 'WAREHOUSE', 'HOST', 'ACCOUNT', 'USER', 'PRIVATE_KEY', 'THREADS'];
        foreach ($names as $name) {
            putenv(sprintf('DBT_KBC_PROD_%s', $name));
        }
    }

    public function testDumpYamlIgnoresExistingProfilesWithJinjaTemplating(): void
    {
        putenv('DBT_KBC_PROD_PRIVATE_KEY=private_key');

        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        $jinjaProfilesYaml = <<<YAML
default:
    target: dev
    outputs:
        dev:
            type: snowflake
            private_key_path: {{ env_var('DBT_KBC_DEV_PRIVATE_KEY_PATH') }}
YAML;
        $fs->dumpFile(sprintf('%s/profiles.yml', $this->dataDir), $jinjaProfilesYaml);

        $logger = new TestLogger();
        $service = new DbtProfilesYaml($logger);
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            LocalSnowflakeProvider::getOutputs(
                ['KBC_DEV_CHOCHO'],
                LocalSnowflakeProvider::getDbtParams(),
            ),
        );

        self::assertTrue($logger->hasWarningThatContains('Could not parse existing profiles.yml'));

        $generated = (array) Yaml::parseFile(sprintf('%s/profiles.yml', $this->dataDir));
        self::assertArrayHasKey('default', $generated);
        self::assertIsArray($generated['default']);
        self::assertArrayHasKey('outputs', $generated['default']);
        self::assertIsArray($generated['default']['outputs']);
        self::assertSame(['kbc_dev_chocho'], array_keys($generated['default']['outputs']));

        putenv('DBT_KBC_PROD_PRIVATE_KEY');
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlMissingDbtProjectFile(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Missing key "profile" in "dbt_project.yml"');

        $fs = new Filesystem();
        $fs->touch(sprintf('%s/dbt_project.yml', $this->dataDir));

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            LocalSnowflakeProvider::getOutputs(
                ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK'],
                LocalSnowflakeProvider::getDbtParams(),
            ),
        );
    }

    /**
     * @dataProvider sourcesProvider
     * @param array<string, array{tables: array<array{name: string, primaryKey: array<string>}>}> $tablesData
     */
    public function testCreateSourceYaml(string $serviceClass, array $tablesData, string $expectedFileSuffix): void
    {
        /** @var SnowflakeDbtSourcesYaml|BigQueryDbtSourcesYaml $service */
        $service = new $serviceClass();

        $freshness = [
            'warn_after' => ['count' => 1, 'period' => 'hour'],
            'error_after' => ['count' => 1, 'period' => 'day'],
        ];

        $service->dumpYaml(
            $this->dataDir,
            $tablesData,
            $freshness,
        );

        foreach ($tablesData as $bucket => $tables) {
            self::assertFileEquals(
                sprintf('%s/models/_sources/%s%s.yml', $this->providerDataDir, $bucket, $expectedFileSuffix),
                sprintf('%s/models/_sources/%s.yml', $this->dataDir, $bucket),
            );
        }
    }

    /**
     * @return Generator<string, array{
     *     serviceClass: class-string<SnowflakeDbtSourcesYaml|BigQueryDbtSourcesYaml>,
     *     tablesData: array<string, array{tables: array<array{name: string, primaryKey: array<string>}>}>,
     *     expectedFileSuffix: string
     * }>
     */
    public function sourcesProvider(): Generator
    {
        yield 'Snowflake' => [
            'serviceClass' => SnowflakeDbtSourcesYaml::class,
            'tablesData' => [
                'bucket-1' => ['tables' => [['name' => 'table1', 'primaryKey' => ['id']]]],
                'bucket-2' => ['tables' => [
                    ['name' => 'table2', 'primaryKey' => ['vatId']],
                    ['name' => 'tableWithCompoundPrimaryKey', 'primaryKey' => ['id', 'vatId']],
                ]],
                'linked-bucket' => ['tables' => [
                    ['name' => 'linkedTable', 'primaryKey' => []],
                ], 'projectId' => '9090'],
            ],
            'expectedFileSuffix' => '',
        ];

        yield 'BigQuery' => [
            'serviceClass' => BigQueryDbtSourcesYaml::class,
            'tablesData' => [
                'bucket-1' => ['tables' => [['name' => 'table1', 'primaryKey' => ['id']]]],
                'bucket-2' => ['tables' => [
                    ['name' => 'table2', 'primaryKey' => ['vatId']],
                    ['name' => 'tableWithCompoundPrimaryKey', 'primaryKey' => ['id', 'vatId']],
                ]],
            ],
            'expectedFileSuffix' => '-bigquery',
        ];
    }
}
