<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Helper;

use DbtTransformation\Helper\DbtDocsHelper;
use PHPUnit\Framework\TestCase;

class DbtDocsHelperTest extends TestCase
{
    public function testMergeHtml(): void
    {
        $manifestJson = '{"metadata":{"foo":"bar"}}';
        $catalogJson = '{"metadata":{"dbt_version":"1.0.6"}}';
        $html = '<html><head></head><body><script type="application/javascript">            
            loadProject=function() {            
                o=[i("manifest","manifest.json"+t),i("catalog","catalog.json"+t)];            
            }
            </script></body></html>';

        $expectedHtml = '<html><head></head><body><script type="application/javascript">            
            loadProject=function() {            
                o=[{label: \'manifest\', data: {"metadata":{"foo":"bar"}}},'
                . '{label: \'catalog\', data: {"metadata":{"dbt_version":"1.0.6"}}}];            
            }
            </script></body></html>';

        $resultHtml = DbtDocsHelper::mergeHtml($html, $catalogJson, $manifestJson);
        self::assertEquals($expectedHtml, $resultHtml);
    }

    public function testGetModelTiming(): void
    {
        $manifestJson = (string) file_get_contents(__DIR__ . '/../data/target/manifest.json');
        $runResultsJson = (string) file_get_contents(__DIR__ . '/../data/target/run_results.json');

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
}
