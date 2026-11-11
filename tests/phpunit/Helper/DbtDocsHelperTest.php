<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Helper;

use DbtTransformation\Helper\DbtDocsHelper;
use Generator;
use PHPUnit\Framework\TestCase;

class DbtDocsHelperTest extends TestCase
{
    /**
     * @dataProvider htmlMergeDataProvider
     */
    public function testMergeHtml(string $html, string $expectedHtml): void
    {
        $manifestJson = '{"metadata":{"foo":"bar"}}';
        $catalogJson = '{"metadata":{"dbt_version":"1.0.6"}}';
        $resultHtml = DbtDocsHelper::mergeHtml($html, $catalogJson, $manifestJson);
        self::assertEquals($expectedHtml, $resultHtml);
    }

    /**
     * @return Generator<string, array{html: string, expectedHtml: string}>
     */
    public function htmlMergeDataProvider(): Generator
    {
        yield 'legacy structure' => [
            'html' => '<html><head></head><body><script type="application/javascript">            
            loadProject=function() {            
                o=[i("manifest","manifest.json"+t),i("catalog","catalog.json"+t)];            
            }
            </script></body></html>',
            'expectedHtml' => '<html><head></head><body><script type="application/javascript">            
            loadProject=function() {            
                o=[{label: \'manifest\', data: {"metadata":{"foo":"bar"}}},'
                . '{label: \'catalog\', data: {"metadata":{"dbt_version":"1.0.6"}}}];            
            }
            </script></body></html>',
        ];

        yield 'new structure' => [
            'html' => '<html><head></head><body><script type="application/javascript">            
            loadProject=function() {            
                n=[o("manifest","manifest.json"+t),o("catalog","catalog.json"+t)];            
            }
            </script></body></html>',
            'expectedHtml' => '<html><head></head><body><script type="application/javascript">            
            loadProject=function() {            
                n=[{label: \'manifest\', data: {"metadata":{"foo":"bar"}}},'
                . '{label: \'catalog\', data: {"metadata":{"dbt_version":"1.0.6"}}}];            
            }
            </script></body></html>',
        ];
    }

    public function testGetModelTiming(): void
    {
        $manifestJson = (string) file_get_contents(__DIR__ . '/../data/target/manifest.json');
        $runResultsJson = (string) file_get_contents(__DIR__ . '/../data/target/run_results.json');

        /** @var array<string, mixed> $manifest */
        $manifest = (array) json_decode($manifestJson, true, 512, JSON_THROW_ON_ERROR);
        /** @var array<string, array<string, mixed>> $runResults */
        $runResults = (array) json_decode($runResultsJson, true, 512, JSON_THROW_ON_ERROR);

        $modelTiming = DbtDocsHelper::getModelTiming($manifest, $runResults);

        $expected = [
            [
                'id' => 'model.beer_analytics.breweries',
                'name' => 'breweries',
                'status' => 'success',
                'thread' => 'Thread-2',
                'timeStarted' => '2023-04-24T15:13:21.862709Z',
                'timeCompleted' => '2023-04-24T15:13:22.552862Z',
                'dependsOn' =>[],
            ],
            [
                'id' => 'model.beer_analytics.beers',
                'name' => 'beers',
                'status' => 'success',
                'thread' => 'Thread-1',
                'timeStarted' => '2023-04-24T15:13:21.830578Z',
                'timeCompleted' => '2023-04-24T15:13:22.548822Z',
                'dependsOn' => [],
            ],
            [
                'id' => 'model.beer_analytics.beers_with_breweries',
                'name' => 'beers_with_breweries',
                'status' => 'success',
                'thread' => 'Thread-4',
                'timeStarted' => '2023-04-24T15:13:22.735716Z',
                'timeCompleted' => '2023-04-24T15:13:24.458116Z',
                'dependsOn' => [
                    0 => 'model.beer_analytics.beers',
                    1 => 'model.beer_analytics.breweries',
                ],
            ],
            [
                'id' => 'model.beer_analytics.orders',
                'name' => 'orders',
                'status' => 'success',
                'thread' => 'Thread-3',
                'timeStarted' => '2023-04-24T15:13:21.964076Z',
                'timeCompleted' => '2023-04-24T15:13:26.685301Z',
                'dependsOn' => [],
            ],
            [
                'id' => 'model.beer_analytics.order_lines',
                'name' => 'order_lines',
                'status' => 'success',
                'thread' => 'Thread-2',
                'timeStarted' => '2023-04-24T15:13:23.233948Z',
                'timeCompleted' => '2023-04-24T15:13:44.583411Z',
                'dependsOn' => [
                    0 => 'model.beer_analytics.beers',
                ],
            ],
            [
                'id' => 'model.beer_analytics.sales',
                'name' => 'sales',
                'status' => 'success',
                'thread' => 'Thread-4',
                'timeStarted' => '2023-04-24T15:13:44.705824Z',
                'timeCompleted' => '2023-04-24T15:13:46.323058Z',
                'dependsOn' => [
                    0 => 'model.beer_analytics.orders',
                    1 => 'model.beer_analytics.order_lines',
                    2 => 'model.beer_analytics.beers_with_breweries',
                ],
            ],
            [
                'id' => 'model.beer_analytics.promo_deliveries',
                'name' => 'promo_deliveries',
                'status' => 'success',
                'thread' => 'Thread-1',
                'timeStarted' => '2023-04-24T15:13:44.709767Z',
                'timeCompleted' => '2023-04-24T15:13:46.328366Z',
                'dependsOn' => [
                    0 => 'model.beer_analytics.orders',
                    1 => 'model.beer_analytics.order_lines',
                    2 => 'model.beer_analytics.beers',
                ],
            ],
        ];

        self::assertSame($expected, $modelTiming);
    }

    public function testGetModelTimingWithSeed(): void
    {
        $manifest = [
            'nodes' => [
                'seed.my_project.country_codes' => [
                    'depends_on' => ['macros' => []],
                ],
                'model.my_project.customers' => [
                    'depends_on' => [
                        'macros' => [],
                        'nodes' => ['seed.my_project.country_codes', 'model.my_project.stg_customers'],
                    ],
                ],
                'test.my_project.not_null_customers_id' => [
                    'depends_on' => [
                        'macros' => ['macro.dbt.test_not_null'],
                        'nodes' => ['model.my_project.customers'],
                    ],
                ],
            ],
        ];
        /** @var array<string, array<string, mixed>> $runResults */
        $runResults = [
            'results' => [
                self::runResult('seed.my_project.country_codes', 'Thread-1', '10:00:00', '10:00:01'),
                self::runResult('model.my_project.customers', 'Thread-2', '10:00:02', '10:00:03'),
                self::runResult('test.my_project.not_null_customers_id', 'Thread-1', '10:00:04', '10:00:05'),
            ],
        ];

        $modelTiming = DbtDocsHelper::getModelTiming($manifest, $runResults);

        self::assertSame([
            [
                'id' => 'seed.my_project.country_codes',
                'name' => 'country_codes',
                'status' => 'success',
                'thread' => 'Thread-1',
                'timeStarted' => '2026-11-11T10:00:00Z',
                'timeCompleted' => '2026-11-11T10:00:01Z',
                'dependsOn' => [],
            ],
            [
                'id' => 'model.my_project.customers',
                'name' => 'customers',
                'status' => 'success',
                'thread' => 'Thread-2',
                'timeStarted' => '2026-11-11T10:00:02Z',
                'timeCompleted' => '2026-11-11T10:00:03Z',
                'dependsOn' => [1 => 'model.my_project.stg_customers'],
            ],
            [
                'id' => 'test.my_project.not_null_customers_id',
                'name' => 'not_null_customers_id',
                'status' => 'success',
                'thread' => 'Thread-1',
                'timeStarted' => '2026-11-11T10:00:04Z',
                'timeCompleted' => '2026-11-11T10:00:05Z',
                'dependsOn' => ['model.my_project.customers'],
            ],
        ], $modelTiming);
    }

    /**
     * @return array<string, mixed>
     */
    private static function runResult(string $uniqueId, string $threadId, string $started, string $completed): array
    {
        return [
            'unique_id' => $uniqueId,
            'status' => 'success',
            'thread_id' => $threadId,
            'timing' => [
                [
                    'name' => 'compile',
                    'started_at' => '2026-11-11T09:59:59Z',
                    'completed_at' => '2026-11-11T09:59:59Z',
                ],
                [
                    'name' => 'execute',
                    'started_at' => '2026-11-11T' . $started . 'Z',
                    'completed_at' => '2026-11-11T' . $completed . 'Z',
                ],
            ],
        ];
    }
}
