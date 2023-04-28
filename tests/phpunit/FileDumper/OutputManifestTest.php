<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\FileDumper;

use DbtTransformation\FileDumper\OutputManifest;
use DbtTransformation\FileDumper\OutputManifest\DbtManifestParser;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\SnowflakeDbAdapter\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

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

    private function getConnectionMock(bool $upperCaseNames = false): Connection
    {
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
                        'name' => $upperCaseNames ? strtoupper('brewery_id') : 'brewery_id',
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
                        'name' => $upperCaseNames ? strtoupper('beer_id') : 'beer_id',
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
                        'name' => $upperCaseNames ? strtoupper('beer_name') : 'beer_name',
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
                        'name' => $upperCaseNames ? strtoupper('brewery_name') : 'brewery_name',
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
                        'name' => $upperCaseNames ? strtoupper('brewery_db_only') : 'brewery_db_only',
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
                        'name' => $upperCaseNames ? strtoupper('beer_id') : 'beer_id',
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
                        'name' => $upperCaseNames ? strtoupper('beer_name') : 'beer_name',
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
                        'name' => $upperCaseNames ? strtoupper('beer_style') : 'beer_style',
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
                        'name' => $upperCaseNames ? strtoupper('beer_db_only') : 'beer_db_only',
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

        return $connectionMock;
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

        $manifestManager = new ManifestManager($this->dataDir);
        $dbtManifestParser = new DbtManifestParser($this->providerDataDir);
        $outputManifest = new OutputManifest(
            $workspaceConfig,
            $this->getConnectionMock(),
            $manifestManager,
            $dbtManifestParser,
            true
        );

        $outputManifest->dump();

        $tableManifestPath1 = $this->dataDir . '/out/tables/beers_with_breweries.manifest';
        $tableManifestPath2 = $this->dataDir . '/out/tables/beers.manifest';
        self::assertFileExists($tableManifestPath1);
        self::assertFileExists($tableManifestPath2);

        /** @var array{
         *     'primary_key': array<string>,
         *     'columns': array<string>,
         *     'metadata': array<int, array<string, string>>,
         *     'column_metadata': array<string, array<string, string>>,
         * } $manifest1
         */
        $manifest1 = (array) json_decode((string) file_get_contents($tableManifestPath1), true);

        self::assertEquals([
            'brewery_id',
            'beer_id',
            'beer_name',
        ], $manifest1['primary_key']);

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
                'key' => 'KBC.description',
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
                'key' => 'KBC.description',
                'value' => 'Name of the brewery',
            ],
            [
                'key' => 'dbt.meta',
                'value' => '[]',
            ],
        ];
        self::assertEquals($expectedColumnMetadata1, $manifest1['column_metadata']['brewery_name']);
    }

    public function testDumpJsonNoQuotes(): void
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

        $manifestManager = new ManifestManager($this->dataDir);
        $dbtManifestParser = new DbtManifestParser($this->providerDataDir);
        $outputManifest = new OutputManifest(
            $workspaceConfig,
            $this->getConnectionMock(),
            $manifestManager,
            $dbtManifestParser,
            false
        );

        $outputManifest->dump();

        $tableManifestPath1 = $this->dataDir . '/out/tables/BEERS_WITH_BREWERIES.manifest';
        $tableManifestPath2 = $this->dataDir . '/out/tables/BEERS.manifest';
        self::assertFileExists($tableManifestPath1);
        self::assertFileExists($tableManifestPath2);

        /** @var array{
         *     'primary_key': array<string>,
         *     'columns': array<string>,
         *     'metadata': array<int, array<string, string>>,
         *     'column_metadata': array<string, array<string, string>>,
         * } $manifest1
         */
        $manifest1 = (array) json_decode((string) file_get_contents($tableManifestPath1), true);

        self::assertEquals([
            'brewery_id',
            'beer_id',
            'beer_name',
        ], $manifest1['primary_key']);

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
                'key' => 'KBC.description',
                'value' => 'Beers joined with their breweries',
            ],
            [
                'key' => 'meta.owner',
                'value' => 'fisa@keboola.com',
            ],
            [
                'key' => 'KBC.name',
                'value' => 'BEERS_WITH_BREWERIES',
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
                'key' => 'KBC.description',
                'value' => 'Name of the brewery',
            ],
            [
                'key' => 'dbt.meta',
                'value' => '[]',
            ],
        ];
        self::assertEquals($expectedColumnMetadata1, $manifest1['column_metadata']['brewery_name']);
    }

    public function testDumpJsonUppercase(): void
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

        $manifestManager = new ManifestManager($this->dataDir);
        $dbtManifestParser = new DbtManifestParser($this->providerDataDir);
        $outputManifest = new OutputManifest(
            $workspaceConfig,
            $this->getConnectionMock(true),
            $manifestManager,
            $dbtManifestParser,
            false
        );

        $outputManifest->dump();

        $tableManifestPath1 = $this->dataDir . '/out/tables/BEERS_WITH_BREWERIES.manifest';
        $tableManifestPath2 = $this->dataDir . '/out/tables/BEERS.manifest';
        self::assertFileExists($tableManifestPath1);
        self::assertFileExists($tableManifestPath2);

        /** @var array{
         *     'primary_key': array<string>,
         *     'columns': array<string>,
         *     'metadata': array<int, array<string, string>>,
         *     'column_metadata': array<string, array<string, string>>,
         * } $manifest1
         */
        $manifest1 = (array) json_decode((string) file_get_contents($tableManifestPath1), true);

        self::assertEquals([
            'BREWERY_ID',
            'BEER_ID',
            'BEER_NAME',
        ], $manifest1['primary_key']);

        $expectedColumns1 = [
            'BREWERY_ID',
            'BEER_ID',
            'BEER_NAME',
            'BREWERY_NAME',
            'BREWERY_DB_ONLY',
        ];
        self::assertEquals($expectedColumns1, $manifest1['columns']);
        $expectedTableMetadata1 = [
            [
                'key' => 'KBC.description',
                'value' => 'Beers joined with their breweries',
            ],
            [
                'key' => 'meta.owner',
                'value' => 'fisa@keboola.com',
            ],
            [
                'key' => 'KBC.name',
                'value' => 'BEERS_WITH_BREWERIES',
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
                'key' => 'KBC.description',
                'value' => 'Name of the brewery',
            ],
            [
                'key' => 'dbt.meta',
                'value' => '[]',
            ],
        ];
        self::assertEquals($expectedColumnMetadata1, $manifest1['column_metadata']['BREWERY_NAME']);
    }

    public function testGetPrimaryKeyColumnNames(): void
    {
        $dbtPrimaryKeys = [
            'brewery_id',
            'beer_id',
        ];

        $columnNames = [
            'BREWERY_ID',
            'BEER_ID',
            'BEER_NAME',
            'BREWERY_NAME',
            'BREWERY_DB_ONLY',
        ];

        $expected = [
            'BREWERY_ID',
            'BEER_ID',
        ];

        self::assertEquals($expected, OutputManifest::getPrimaryKeyColumnNames($dbtPrimaryKeys, $columnNames));
    }
}
