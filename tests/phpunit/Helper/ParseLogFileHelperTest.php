<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Helper;

use DbtTransformation\Helper\ParseLogFileHelper;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ParseLogFileHelperTest extends TestCase
{
    protected string $providerDataDir = __DIR__ . '/../data';

    public function testGetSqlsFromDbtLogFile(): void
    {
        $sqls = (new ParseLogFileHelper(sprintf('%s/dbt.log', $this->providerDataDir)))->getSqls();
        $expectedSqls = $this->getExpectedSqls();
        foreach ($sqls as $key => $sql) {
            $this->assertEquals($expectedSqls[$key], $sql);
        }
    }

    public function testMissingLogData(): void
    {
        $temp = new Temp('missing-log-data');
        $fs = new Filesystem();
        $fileName = $temp->getTmpFolder() . '/dbt.log';
        $content = <<<LOG
"some string"
{"data":{"sql": "SELECT 1"}}
LOG;
        $fs->dumpFile($fileName, $content);

        $expected = [
            'SELECT 1',
        ];

        $sqls = (new ParseLogFileHelper($fileName))->getSqls();

        foreach ($sqls as $key => $sql) {
            $this->assertEquals($expected[$key], $sql);
        }
    }

    /**
     * @return string[]
     */
    private function getExpectedSqls(): array
    {
        return [
            'show terse schemas in database "KEBOOLA_3194"
    limit 10000',
            'show terse objects in "KEBOOLA_3194"."WORKSPACE_380649405"',
            'create or replace  view "KEBOOLA_3194"."WORKSPACE_380649405"."stg_model" 
  
   as (
    with source as (
        
        select * from KEBOOLA_3194.WORKSPACE_380649405."test"
        
    ),
    
    renamed as (
        
        select
            "id",
            "col2",
            "col3",
            "col4"
        from source
    
    )
    
    select * from renamed
  );',
            'create or replace  view "KEBOOLA_3194"."WORKSPACE_380649405"."fct_model" 
  
   as (
    -- Use the `ref` function to select from other models

select *
from "KEBOOLA_3194"."WORKSPACE_380649405"."stg_model"
where "id" = 1
  );',
        ];
    }
}
