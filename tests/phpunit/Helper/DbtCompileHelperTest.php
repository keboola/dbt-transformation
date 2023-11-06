<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Helper;

use DbtTransformation\Helper\DbtCompileHelper;
use Keboola\Component\UserException;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class DbtCompileHelperTest extends TestCase
{
    public function testGetCompiledSqlFiles(): void
    {
        $dataDir = new SplFileInfo(__DIR__ . '/../data/artifacts/in/dbt run');
        $compiled = DbtCompileHelper::getCompiledSqlFilesContent($dataDir->getPathname());

        self::assertArrayHasKey('source_not_null_in.c-test-bucket_test__id_.sql', $compiled);
        self::assertArrayHasKey('source_unique_in.c-test-bucket_test__id_.sql', $compiled);
        self::assertArrayHasKey('fct_model.sql', $compiled);
        self::assertArrayHasKey('stg_model.sql', $compiled);

        self::assertStringMatchesFormat(
            '%Afrom "SAPI_%d"."in.c-test-bucket"."test"%A',
            (string) $compiled['source_not_null_in.c-test-bucket_test__id_.sql'],
        );

        self::assertStringMatchesFormat(
            '%A"id" as unique_field,%A',
            (string) $compiled['source_unique_in.c-test-bucket_test__id_.sql'],
        );
        self::assertStringMatchesFormat(
            '%Afrom "SAPI_%d"."in.c-test-bucket"."test"%A',
            (string) $compiled['source_unique_in.c-test-bucket_test__id_.sql'],
        );

        self::assertStringMatchesFormat(
            '%Afrom "SAPI_%d"."WORKSPACE_%d"."stg_model"%A',
            (string) $compiled['fct_model.sql'],
        );

        self::assertStringMatchesFormat(
            '%Aselect * from "SAPI_%d"."in.c-test-bucket"."test"%A',
            (string) $compiled['stg_model.sql'],
        );
    }

    public function testGetCompiledSqlFilesNotFound(): void
    {
        $temp = new Temp();

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Compiled SQL files not found. Run the component with "dbt run" step first.');

        DbtCompileHelper::getCompiledSqlFilesContent($temp->getTmpFolder());
    }
}
