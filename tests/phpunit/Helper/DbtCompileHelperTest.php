<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Helper;

use DbtTransformation\Helper\DbtCompileHelper;
use DbtTransformation\Service\ArtifactsService;
use Keboola\Component\UserException;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;

class DbtCompileHelperTest extends TestCase
{
    public function testGetCompiledSqlFiles(): void
    {
        $dataDir = __DIR__ . '/../data';
        $compiled = DbtCompileHelper::getCompiledSqlFiles($dataDir);

        self::assertArrayHasKey('source_not_null_in.c-test-bucket_test__id_.sql', $compiled);
        self::assertArrayHasKey('source_unique_in.c-test-bucket_test__id_.sql', $compiled);
        self::assertArrayHasKey('fct_model.sql', $compiled);
        self::assertArrayHasKey('stg_model.sql', $compiled);

        self::assertStringContainsString(
            'from "SAPI_9317"."in.c-test-bucket"."test"',
            (string) $compiled['source_not_null_in.c-test-bucket_test__id_.sql']
        );

        self::assertStringContainsString(
            '"id" as unique_field,',
            (string) $compiled['source_unique_in.c-test-bucket_test__id_.sql']
        );
        self::assertStringContainsString(
            'from "SAPI_9317"."in.c-test-bucket"."test"',
            (string) $compiled['source_unique_in.c-test-bucket_test__id_.sql']
        );

        self::assertStringContainsString(
            'from "SAPI_9317"."WORKSPACE_875822722"."stg_model"',
            (string) $compiled['fct_model.sql']
        );

        self::assertStringContainsString(
            'select * from "SAPI_9317"."in.c-test-bucket"."test"',
            (string) $compiled['stg_model.sql']
        );
    }

    public function testGetCompiledSqlFilesNotFound(): void
    {
        $temp = new Temp();

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Compiled SQL files not found');

        DbtCompileHelper::getCompiledSqlFiles($temp->getTmpFolder());
    }
}
