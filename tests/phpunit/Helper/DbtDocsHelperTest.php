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
        $manifestJson = (string) file_get_contents(__DIR__ . '/../data/manifest.json');
        $runResultsJson = (string) file_get_contents(__DIR__ . '/../data/run_results.json');

        $manifest = (array) json_decode($manifestJson, true, 512, JSON_THROW_ON_ERROR);
        $runResults = (array) json_decode($runResultsJson, true, 512, JSON_THROW_ON_ERROR);

        $modelTiming = DbtDocsHelper::getModelTiming($manifest, $runResults);

        $expected = [
            [
                'id' => 'model.my_new_project.stg_model',
                'name' => 'stg_model',
                'status' => 'success',
                'thread' => 'Thread-1',
                'timeStarted' => '2022-09-13T12:47:36.503145Z',
                'timeCompleted' => '2022-09-13T12:47:37.189261Z',
                'dependsOn' => [],
            ],
            [
                'id' => 'model.my_new_project.fct_model',
                'name' => 'fct_model',
                'status' => 'success',
                'thread' => 'Thread-1',
                'timeStarted' => '2022-09-13T12:47:37.384817Z',
                'timeCompleted' => '2022-09-13T12:47:38.020192Z',
                'dependsOn' => [
                    'model.my_new_project.stg_model',
                ],
            ],
        ];

        self::assertSame($expected, $modelTiming);
    }
}
