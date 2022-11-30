<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\FileDumper;

use DbtTransformation\FileDumper\OutputManifest;
use DbtTransformation\FileDumper\OutputManifest\DbtManifestParser;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\SnowflakeDbAdapter\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class OutputManifestTest extends TestCase
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
        $workspaceConfig = [
            'host' => 'host',
            'port' => 'port',
            'warehouse' => 'warehouse',
            'database' => 'database',
            'schema' => 'schema',
            'user' => 'user',
            'password' => 'password',
        ];

        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAll'])
            ->getMock();

        $connectionMock
            ->expects(self::any())
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [
                    [
                        'name' => 'brewery_id',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                    [
                        'name' => 'beer_id',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                    [
                        'name' => 'beer_name',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                    [
                        'name' => 'brewery_name',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                    [
                        'name' => 'brewery_db_only',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                ],
                [
                    [
                        'name' => 'beer_id',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                    [
                        'name' => 'beer_name',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                    [
                        'name' => 'beer_style',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                    [
                        'name' => 'beer_db_only',
                        'type' => 'VARCHAR(16777216)',
                        'kind' => 'COLUMN',
                        'null?' => 'Y',
                        'default' => null,
                        'primary_key' => 'N',
                        'unique_key' => 'N',
                        'check' => null,
                        'expression' => null,
                        'comment' => null,
                        'policy_name' => null,
                    ],
                ]
            );

        $manifestManager = new ManifestManager($this->dataDir);
        $dbtManifestParser = new DbtManifestParser($this->providerDataDir);
        $outputManifest = new OutputManifest($workspaceConfig, $connectionMock, $manifestManager, $dbtManifestParser);

        $outputManifest->dump();

        $tableManifestPath1 = $this->dataDir . '/out/tables/beers_with_breweries.manifest';
        $tableManifestPath2 = $this->dataDir . '/out/tables/beers.manifest';
        self::assertFileExists($tableManifestPath1);
        self::assertFileExists($tableManifestPath2);

        $manifest1 = json_decode((string) file_get_contents($tableManifestPath1), true);
        $expectedColumns1 = [
            'brewery_id',
            'beer_id',
            'beer_name',
            'brewery_name',
            'brewery_db_only',
        ];
        self::assertEquals($expectedColumns1, $manifest1['columns']);
        $expectedTableMetadata1 = [
            [
                'key' => 'description',
                'value' => 'Beers joined with their breweries',
            ],
            [
                'key' => 'meta.owner',
                'value' => 'fisa@keboola.com',
            ],
            [
                'key' => 'KBC.name',
                'value' => 'beers_with_breweries',
            ],
            [
                'key' => 'KBC.datatype.backend',
                'value' => 'snowflake',
            ],
        ];
        self::assertEquals($expectedTableMetadata1, $manifest1['metadata']);

        $expectedColumnMetadata1 = [
            [
                'key' => 'KBC.datatype.type',
                'value' => 'VARCHAR',
            ],
            [
                'key' => 'KBC.datatype.nullable',
                'value' => true,
            ],
            [
                'key' => 'KBC.datatype.basetype',
                'value' => 'STRING',
            ],
            [
                'key' => 'KBC.datatype.length',
                'value' => '16777216',
            ],
            [
                'key' => 'description',
                'value' => 'Name of the brewery',
            ],
            [
                'key' => 'dbt.meta',
                'value' => '[]',
            ],
        ];
        self::assertEquals($expectedColumnMetadata1, $manifest1['column_metadata']['brewery_name']);
    }
}
