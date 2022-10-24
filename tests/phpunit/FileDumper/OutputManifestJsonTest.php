<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\FileDumper;

use DbtTransformation\FileDumper\OutputManifest\ManifestConverter;
use DbtTransformation\FileDumper\OutputManifestJson;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class OutputManifestJsonTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../../data';
    protected string $providerDataDir = __DIR__ . '/../data';

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in($this->dataDir));
    }

    public function testDumpJson(): void
    {
        $outputManifest = new OutputManifestJson($this->dataDir);
        $manifestConverter = new ManifestConverter($this->providerDataDir);
        foreach ($manifestConverter->toOutputTables() as $tableName => $manifestData) {
            $outputManifest->dumpJson($tableName, $manifestData);
        }

        $tableManifestPath1 = $this->dataDir . '/out/tables/beers_with_breweries.manifest';
        $tableManifestPath2 = $this->dataDir . '/out/tables/beers.manifest';
        self::assertFileExists($tableManifestPath1);
        self::assertFileExists($tableManifestPath2);
    }
}
