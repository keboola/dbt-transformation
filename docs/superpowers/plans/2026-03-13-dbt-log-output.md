# dbt.log Output Feature Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a `showDbtLog` config option and `DbtLogService` that incrementally reads `logs/dbt.log` and outputs raw NDJSON lines to the component logger, including on failure via a `finally` block.

**Architecture:** New `DbtLogService` encapsulates incremental file reading with byte offset tracking. Component instantiates it before the step loop, calls it after each step, and uses a `try/finally` block to ensure output on failure. New boolean config option `showDbtLog` gates the behavior.

**Tech Stack:** PHP 8.3, PHPUnit 9, Symfony Config, PSR-3 Logger, `ColinODell\PsrTestLogger\TestLogger`

---

## Chunk 1: DbtLogService + Config

### Task 1: DbtLogService — Unit Tests

**Files:**
- Create: `tests/phpunit/Service/DbtLogServiceTest.php`

- [ ] **Step 1: Write the test file with all test cases**

```php
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
        file_put_contents($logFile, '{"level":"info","msg":"Starting"}' . "\n" . '{"level":"info","msg":"Done"}' . "\n");

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
        file_put_contents($logFile, '{"level":"info","msg":"Line 1"}' . "\n\n\n" . '{"level":"info","msg":"Line 2"}' . "\n");

        $logger = new TestLogger();
        $service = new DbtLogService($logger, $logFile);
        $service->log();

        self::assertCount(2, $logger->records);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php vendor/bin/phpunit tests/phpunit/Service/DbtLogServiceTest.php`
Expected: FAIL — class `DbtLogService` not found

- [ ] **Step 3: Commit test file**

```bash
git add tests/phpunit/Service/DbtLogServiceTest.php
git commit -m "test: add DbtLogService unit tests (red)"
```

### Task 2: DbtLogService — Implementation

**Files:**
- Create: `src/Service/DbtLogService.php`

- [ ] **Step 1: Write the DbtLogService class**

```php
<?php

declare(strict_types=1);

namespace DbtTransformation\Service;

use Psr\Log\LoggerInterface;

class DbtLogService
{
    private int $offset = 0;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $logFilePath,
    ) {
    }

    public function log(): void
    {
        if (!file_exists($this->logFilePath)) {
            return;
        }

        $fileSize = filesize($this->logFilePath);
        if ($fileSize === false) {
            return;
        }

        if ($fileSize < $this->offset) {
            $this->offset = 0;
        }

        $handle = fopen($this->logFilePath, 'r');
        if ($handle === false) {
            return;
        }

        fseek($handle, $this->offset);

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line !== '') {
                $this->logger->info($line);
            }
        }

        $this->offset = (int) ftell($handle);
        fclose($handle);
    }
}
```

- [ ] **Step 2: Run tests to verify they pass**

Run: `php vendor/bin/phpunit tests/phpunit/Service/DbtLogServiceTest.php`
Expected: All 7 tests PASS

- [ ] **Step 3: Commit**

```bash
git add src/Service/DbtLogService.php
git commit -m "feat: add DbtLogService for incremental dbt.log reading"
```

### Task 3: Config Option — showDbtLog

**Files:**
- Modify: `src/Configuration/ConfigDefinition.php:30-35`
- Modify: `src/Config.php:54-57`
- Modify: `tests/phpunit/ConfigDefinition/ConfigDefinitionTest.php`

- [ ] **Step 1: Add test case for showDbtLog config**

In `tests/phpunit/ConfigDefinition/ConfigDefinitionTest.php`, add a new yield in `validConfigsData()` after the `'config with show executed SQLs parameter'` yield (after line 135):

```php
        yield 'config with show dbt log parameter' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'executeSteps' => [['step' => 'dbt run', 'active' => true]],
                    ],
                    'showDbtLog' => true,
                ],
            ],
        ];
```

Update `addDefaultValues()` method — add after the `showExecutedSqls` default block (after line 818):

```php
        if (!array_key_exists('showDbtLog', $configData['parameters'])) {
            $configData['parameters']['showDbtLog'] = false;
        }
```

Also add a direct getter test method in the same test class:

```php
    public function testShowDbtLogDefaultsFalse(): void
    {
        $config = new Config([
            'action' => 'run',
            'parameters' => [
                'git' => ['repo' => 'https://github.com/my-repo'],
                'dbt' => ['executeSteps' => [['step' => 'dbt run', 'active' => true]]],
            ],
        ], new ConfigDefinition());
        $this->assertFalse($config->showDbtLog());
    }

    public function testShowDbtLogReturnsTrue(): void
    {
        $config = new Config([
            'action' => 'run',
            'parameters' => [
                'git' => ['repo' => 'https://github.com/my-repo'],
                'dbt' => ['executeSteps' => [['step' => 'dbt run', 'active' => true]]],
                'showDbtLog' => true,
            ],
        ], new ConfigDefinition());
        $this->assertTrue($config->showDbtLog());
    }
```

- [ ] **Step 2: Run config tests to verify they fail**

Run: `php vendor/bin/phpunit tests/phpunit/ConfigDefinition/ConfigDefinitionTest.php`
Expected: FAIL — `showDbtLog` not recognized in config / `showDbtLog()` method not found

- [ ] **Step 3: Add showDbtLog to ConfigDefinition**

In `src/Configuration/ConfigDefinition.php`, add after the `showExecutedSqls` block (after line 35):

```php
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
            ->booleanNode('showDbtLog')
            ->defaultFalse()
            ->end();
```

- [ ] **Step 4: Add getShowDbtLog() to Config**

In `src/Config.php`, add after the `showSqls()` method (after line 57):

```php
    public function showDbtLog(): bool
    {
        return (bool) $this->getValue(['parameters', 'showDbtLog']);
    }
```

- [ ] **Step 5: Run config tests to verify they pass**

Run: `php vendor/bin/phpunit tests/phpunit/ConfigDefinition/ConfigDefinitionTest.php`
Expected: All tests PASS

- [ ] **Step 6: Commit**

```bash
git add src/Configuration/ConfigDefinition.php src/Config.php tests/phpunit/ConfigDefinition/ConfigDefinitionTest.php
git commit -m "feat: add showDbtLog config option"
```

### Task 4: Component Integration

**Files:**
- Modify: `src/Component.php:80-107`

- [ ] **Step 1: Add DbtLogService import**

In `src/Component.php`, add to the use statements (after line 24, the `DbtService` import):

```php
use DbtTransformation\Service\DbtLogService;
```

- [ ] **Step 2: Modify the run() method**

Replace **only** the step loop (lines 97-99) in `run()`:

```php
        foreach ($executeSteps as $step) {
            $this->executeStep($step, $provider->getDwhConnectionType());
        }
```

with the following (the `showSqls` and output manifest blocks at lines 100-106 remain unchanged):

```php
        $dbtLogService = new DbtLogService($this->getLogger(), $this->projectPath . '/logs/dbt.log');

        try {
            foreach ($executeSteps as $step) {
                $this->executeStep($step, $provider->getDwhConnectionType());

                if ($config->showDbtLog()) {
                    $dbtLogService->log();
                }
            }
        } finally {
            if ($config->showDbtLog()) {
                $dbtLogService->log();
            }
        }
```

The existing post-loop logic remains as-is after this block:
```php
        if ($config->showSqls()) {
            $this->logExecutedSqls();
        }

        if (!$config->hasRemoteDwh()) {
            $this->getOutputManifest($config->getWorkspaceCredentials())->dump($config->getExpectedOutputTables());
        }
```

Note: `showExecutedSqls` and output manifest logic stay outside `try/finally` — they only run on success. The `finally` block ensures dbt.log lines from failed steps are captured before the exception propagates.

- [ ] **Step 3: Run phpstan to verify no type errors**

Run: `php vendor/bin/phpstan analyse --no-progress`
Expected: No errors

- [ ] **Step 4: Run full phpunit test suite to verify no regressions**

Run: `php vendor/bin/phpunit tests/phpunit/`
Expected: All tests PASS

- [ ] **Step 5: Commit**

```bash
git add src/Component.php
git commit -m "feat: integrate DbtLogService into Component execute loop"
```
