<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service\DbtYamlCreateService;

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
    }

    /**
     * Ensure Snowflake private key auth results in private_key_path (+ passphrase) in profiles.yml
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlWithSnowflakePrivateKey(): void
    {
        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        // Trigger key-based params
        putenv('DBT_KBC_PROD_PRIVATE_KEY_PATH=/tmp/key');
        putenv('DBT_KBC_PROD_PRIVATE_KEY_PASSPHRASE=secret');

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
            sprintf('%s/expectedProfilesSnowflakeWithPrivateKey.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir),
        );
    }

    /**
     * Remote Snowflake default (password)
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlWithRemoteSnowflakePassword(): void
    {
        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        // Ensure password mode
        putenv('DBT_KBC_PROD_PRIVATE_KEY_PATH');
        putenv('DBT_KBC_PROD_PRIVATE_KEY_PASSPHRASE');

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            RemoteSnowflakeProvider::getOutputs(
                ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK'],
                RemoteSnowflakeProvider::getDbtParams(),
            ),
        );

        self::assertFileEquals(
            sprintf('%s/expectedProfiles.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir),
        );
    }

    /**
     * Remote Snowflake with private key
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlWithRemoteSnowflakePrivateKey(): void
    {
        $fs = new Filesystem();
        $fs->copy(
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            sprintf('%s/dbt_project.yml', $this->dataDir),
        );

        // Trigger key-based params
        putenv('DBT_KBC_PROD_PRIVATE_KEY_PATH=/tmp/key');
        putenv('DBT_KBC_PROD_PRIVATE_KEY_PASSPHRASE=secret');

        $service = new DbtProfilesYaml();
        $service->dumpYaml(
            $this->dataDir,
            $this->dataDir,
            RemoteSnowflakeProvider::getOutputs(
                ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK'],
                RemoteSnowflakeProvider::getDbtParams(),
            ),
        );

        self::assertFileEquals(
            sprintf('%s/expectedProfilesSnowflakeWithPrivateKey.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir),
        );
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
