<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\FileDumper\OutputManifest;

use DbtTransformation\FileDumper\OutputManifest\ManifestConverter;
use PHPUnit\Framework\TestCase;

class ManifestConverterTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../../../data';
    protected string $providerDataDir = __DIR__ . '/../../data';

    public function testToOutputTables(): void
    {
        $converter = new ManifestConverter($this->providerDataDir);
        $outputTables = iterator_to_array($converter->toOutputTables());

        $manifest1 = $outputTables['beers_with_breweries'];
        $manifest2 = $outputTables['beers'];

        self::assertArrayHasKey('columns', $manifest1);
        self::assertArrayHasKey('primary_key', $manifest1);
        self::assertArrayHasKey('metadata', $manifest1);
        self::assertArrayHasKey('column_metadata', $manifest1);
        self::assertEquals('brewery_id', $manifest1['columns'][0]);

        self::assertArrayHasKey('columns', $manifest2);
        self::assertArrayHasKey('primary_key', $manifest2);
        self::assertArrayHasKey('metadata', $manifest2);
        self::assertArrayHasKey('column_metadata', $manifest2);
        self::assertEquals('beer_id', $manifest2['columns'][0]);
    }
}
