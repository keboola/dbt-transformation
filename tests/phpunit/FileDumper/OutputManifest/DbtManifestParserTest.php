<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\FileDumper\OutputManifest;

use DbtTransformation\FileDumper\OutputManifest\DbtManifestParser;
use Keboola\Temp\Temp;
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
        /** @var array<string, array<array-key, array<string, string>>> $columnMetadata */
        $columnMetadata = $manifest1['column_metadata'];
        self::assertEquals('KBC.description', $columnMetadata['brewery_id'][0]['key']);
        self::assertEquals(
            'The unique identifier for the brewery',
            $columnMetadata['brewery_id'][0]['value']
        );
    }

    public function testParseUnifyCase(): void
    {
        $temp = new Temp();
        $tmpFolder = $temp->getTmpFolder();

        $manifest = [
            'nodes' => [
                'model.beer_analytics.beers_with_breweries' => [
                    'resource_type' => 'model',
                    'name' => 'beers_with_breweries',
                    'columns' => [
                        'BREWERY_ID' => [
                            'name' => 'BREWERY_ID',
                            'description' => 'The unique identifier for the brewery',
                        ],
                    ],
                ],
            ],
        ];

        mkdir(sprintf('%s/target', $tmpFolder));
        file_put_contents(sprintf('%s/target/manifest.json', $tmpFolder), json_encode($manifest));

        $converter = new DbtManifestParser($tmpFolder);
        $outputTables = $converter->parse();
        /** @var array<string, array<array-key, array<string, string>>> $columnsMetadata */
        $columnsMetadata = $outputTables['beers_with_breweries']['column_metadata'];

        self::assertSame(['brewery_id'], array_keys($columnsMetadata));
        $metadata = (array) array_shift($columnsMetadata['brewery_id']);
        self::assertSame('KBC.description', $metadata['key']);
        self::assertSame('The unique identifier for the brewery', $metadata['value']);
    }
}
