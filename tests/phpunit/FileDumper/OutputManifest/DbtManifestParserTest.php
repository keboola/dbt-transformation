<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\FileDumper\OutputManifest;

use DbtTransformation\FileDumper\OutputManifest\DbtManifestParser;
use PHPUnit\Framework\TestCase;

class DbtManifestParserTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../../../data';
    protected string $providerDataDir = __DIR__ . '/../../data';

    public function testParse(): void
    {
        $converter = new DbtManifestParser($this->providerDataDir);
        $outputTables = $converter->parse();

        $manifest1 = $outputTables['beers_with_breweries'];
        $manifest2 = $outputTables['beers'];

        self::assertArrayHasKey('columns', $manifest1);
        self::assertArrayHasKey('primary_key', $manifest1);
        self::assertArrayHasKey('metadata', $manifest1);
        self::assertArrayHasKey('column_metadata', $manifest1);
        self::assertEquals('brewery_id', $manifest1['columns'][0]);
        self::assertEquals([
            'brewery_id',
            'beer_id',
            'beer_name',
        ], $manifest1['primary_key']);

        self::assertArrayHasKey('columns', $manifest2);
        self::assertArrayHasKey('primary_key', $manifest2);
        self::assertArrayHasKey('metadata', $manifest2);
        self::assertArrayHasKey('column_metadata', $manifest2);
        self::assertEquals('beer_id', $manifest2['columns'][0]);
        self::assertEquals('KBC.description', $manifest1['column_metadata']['brewery_id'][0]['key']);
        self::assertEquals(
            'The unique identifier for the brewery',
            $manifest1['column_metadata']['brewery_id'][0]['value']
        );
    }
}
