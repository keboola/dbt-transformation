<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\SyncAction;

use DbtTransformation\SyncAction\DocsHelper;
use PHPUnit\Framework\TestCase;

class DocsHelperTest extends TestCase
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
                o=[{label: \'manifest\', data: "{"metadata":{"foo":"bar"}}"},'
                . '{label: \'catalog\', data: "{"metadata":{"dbt_version":"1.0.6"}}"}];            
            }
            </script></body></html>';

        $resultHtml = DocsHelper::mergeHtml($html, $catalogJson, $manifestJson);
        self::assertEquals($expectedHtml, $resultHtml);
    }
}
