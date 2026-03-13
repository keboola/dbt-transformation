<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use ColinODell\PsrTestLogger\TestLogger;
use DbtTransformation\Service\DbtLogService;
use PHPUnit\Framework\TestCase;

class DbtLogServiceTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/dbt-log-service-test-' . uniqid();
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tmpDir . '/*');
        if ($files !== false) {
            array_map('unlink', $files);
        }
        rmdir($this->tmpDir);
    }

    public function testReadsAllLines(): void
    {
        $logFile = $this->tmpDir . '/dbt.log';
        $content = '{"level":"info","msg":"Starting"}' . "\n"
            . '{"level":"info","msg":"Done"}' . "\n";
        file_put_contents($logFile, $content);

        $logger = new TestLogger();
        $service = new DbtLogService($logger, $logFile);
        $service->log();

        self::assertTrue($logger->hasInfoThatContains('{"level":"info","msg":"Starting"}'));
        self::assertTrue($logger->hasInfoThatContains('{"level":"info","msg":"Done"}'));
    }

    public function testIncrementalReads(): void
    {
        $logFile = $this->tmpDir . '/dbt.log';
        file_put_contents($logFile, '{"level":"info","msg":"Step 1"}' . "\n");

        $logger = new TestLogger();
        $service = new DbtLogService($logger, $logFile);
        $service->log();

        self::assertCount(1, $logger->records);
        self::assertTrue($logger->hasInfoThatContains('Step 1'));

        // Append more lines
        file_put_contents($logFile, '{"level":"info","msg":"Step 2"}' . "\n", FILE_APPEND);
        $service->log();

        self::assertCount(2, $logger->records);
        self::assertTrue($logger->hasInfoThatContains('Step 2'));
    }

    public function testMissingFile(): void
    {
        $logger = new TestLogger();
        $service = new DbtLogService($logger, $this->tmpDir . '/nonexistent.log');
        $service->log();

        self::assertEmpty($logger->records);
    }

    public function testEmptyFile(): void
    {
        $logFile = $this->tmpDir . '/dbt.log';
        file_put_contents($logFile, '');

        $logger = new TestLogger();
        $service = new DbtLogService($logger, $logFile);
        $service->log();

        self::assertEmpty($logger->records);
    }

    public function testMultiStepSimulation(): void
    {
        $logFile = $this->tmpDir . '/dbt.log';
        $logger = new TestLogger();
        $service = new DbtLogService($logger, $logFile);

        // Step 1: dbt deps
        file_put_contents($logFile, '{"level":"info","msg":"Installing deps"}' . "\n");
        $service->log();
        self::assertCount(1, $logger->records);

        // Step 2: dbt run
        file_put_contents($logFile, '{"level":"info","msg":"Running models"}' . "\n", FILE_APPEND);
        $service->log();
        self::assertCount(2, $logger->records);

        // Step 3: dbt test
        file_put_contents($logFile, '{"level":"info","msg":"Running tests"}' . "\n", FILE_APPEND);
        $service->log();
        self::assertCount(3, $logger->records);
    }

    public function testFileTruncation(): void
    {
        $logFile = $this->tmpDir . '/dbt.log';
        file_put_contents($logFile, '{"level":"info","msg":"Old content that is long enough"}' . "\n");

        $logger = new TestLogger();
        $service = new DbtLogService($logger, $logFile);
        $service->log();

        self::assertCount(1, $logger->records);

        // Truncate and write shorter content
        file_put_contents($logFile, '{"level":"info","msg":"New"}' . "\n");
        $service->log();

        self::assertCount(2, $logger->records);
        self::assertTrue($logger->hasInfoThatContains('New'));
    }

    public function testSkipsEmptyLines(): void
    {
        $logFile = $this->tmpDir . '/dbt.log';
        $content = '{"level":"info","msg":"Line 1"}' . "\n\n\n"
            . '{"level":"info","msg":"Line 2"}' . "\n";
        file_put_contents($logFile, $content);

        $logger = new TestLogger();
        $service = new DbtLogService($logger, $logFile);
        $service->log();

        self::assertCount(2, $logger->records);
    }
}
