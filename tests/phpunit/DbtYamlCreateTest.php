<?php

declare(strict_types=1);

namespace DbtTransformation\Tests;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
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
    public function testCreateProfileYaml(): void {
        $service = new DbtProfilesYamlCreateService();
        $service->dumpYaml(
            $this->dataDir,
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK']
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
            sprintf('%s/non-exist.yml', $this->providerDataDir),
            ['KBC_DEV_CHOCHO', 'KBC_DEV_PADAK']
        );
    }

    /**
     * @throws \JsonException
     */
    public function testCreateSourceYaml(): void {
        $service = new DbtSourceYamlCreateService();

        $tablesData = [
            'bucket-1' => [['id' => 'table1', 'primaryKey' => ['id']]],
            'bucket-2' => [
                ['id' => 'table2', 'primaryKey' => ['vatId']],
                ['id' => 'table3', 'primaryKey' => []],
            ],
        ];

        $service->dumpYaml(
            $this->dataDir,
            $this->getConfig()['parameters']['dbt']['sourceName'],
            $tablesData
        );

        foreach ($tablesData as $bucket => $tables) {
            self::assertFileEquals(
                sprintf("%s/models/_sources/%s.yml", $this->providerDataDir, $bucket),
                sprintf("%s/models/_sources/%s.yml", $this->dataDir, $bucket)
            );
        }
    }

    /**
     * @return array<string, mixed>
     * @throws \JsonException
     */
    protected function getConfig(): array
    {
        $configJson = file_get_contents(sprintf('%s/config.json', $this->providerDataDir));
        if ($configJson === false) {
            throw new RuntimeException('Failed to get contents of config.json');
        }

        return json_decode(
            $configJson,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
