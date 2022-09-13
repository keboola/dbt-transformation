<?php

declare(strict_types=1);

namespace DbtTransformation\Tests;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use DbtTransformation\DwhProvider\LocalSnowflakeProvider;
use DbtTransformation\Tests\Command\GenerateProfilesAndSourcesCommandTest;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DbtYamlCreateTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../data';
    protected string $providerDataDir = __DIR__ . '/data';

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
            sprintf('%s/dbt_project.yml', $this->dataDir)
        );

        $service = new DbtProfilesYamlCreateService();
        $service->dumpYaml(
            $this->dataDir,
            LocalSnowflakeProvider::getOutputs(
                ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK'],
                LocalSnowflakeProvider::getDbtParams()
            )
        );

        self::assertFileEquals(
            sprintf('%s/expectedProfiles.yml', $this->providerDataDir),
            sprintf('%s/profiles.yml', $this->dataDir)
        );
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlMissingDbtProjectFile(): void
    {
        $this->expectException(UserException::class);
        $this->expectErrorMessage('Missing file "dbt_project.yml" in your project root');

        $service = new DbtProfilesYamlCreateService();
        $service->dumpYaml(
            $this->dataDir,
            LocalSnowflakeProvider::getOutputs(
                ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK'],
                LocalSnowflakeProvider::getDbtParams()
            )
        );
    }

    /**
     * @throws \JsonException
     */
    public function testCreateSourceYaml(): void
    {
        $service = new DbtSourceYamlCreateService();

        $tablesData = [
            'bucket-1' => [['name' => 'table1', 'primaryKey' => ['id']]],
            'bucket-2' => [
                ['name' => 'table2', 'primaryKey' => ['vatId']],
                ['name' => 'table3', 'primaryKey' => []],
            ],
        ];

        $service->dumpYaml(
            $this->dataDir,
            $tablesData
        );

        foreach ($tablesData as $bucket => $tables) {
            self::assertFileEquals(
                sprintf('%s/models/_sources/%s.yml', $this->providerDataDir, $bucket),
                sprintf('%s/models/_sources/%s.yml', $this->dataDir, $bucket)
            );
        }
    }
}
