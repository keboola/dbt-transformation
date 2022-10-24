<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\FileDumper;

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
        $outputManifest = new OutputManifestJson($this->dataDir, $this->providerDataDir);
        $outputManifest->dumpJson();

        $tableManifestPath1 = $this->dataDir . '/out/tables/beers_with_breweries.manifest';
        $tableManifestPath2 = $this->dataDir . '/out/tables/beers.manifest';
        self::assertFileExists($tableManifestPath1);
        self::assertFileExists($tableManifestPath2);

        $manifest1 = json_decode((string) file_get_contents($tableManifestPath1), true);
        $manifest2 = json_decode((string) file_get_contents($tableManifestPath2), true);

        self::assertArrayHasKey('columns', $manifest1);
        self::assertArrayHasKey('primary_key', $manifest1);
        self::assertArrayHasKey('metadata', $manifest1);
        self::assertArrayHasKey('column_metadata', $manifest1);

        self::assertArrayHasKey('columns', $manifest2);
        self::assertArrayHasKey('primary_key', $manifest2);
        self::assertArrayHasKey('metadata', $manifest2);
        self::assertArrayHasKey('column_metadata', $manifest2);
    }
}
