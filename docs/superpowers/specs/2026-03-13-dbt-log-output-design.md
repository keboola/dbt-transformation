# Design: Parse dbt.log and Write to Component Output

## Problem

When a dbt transformation runs, detailed execution logs are written to `logs/dbt.log` (NDJSON format) but are not surfaced to the user unless `showExecutedSqls` is enabled (which only extracts SQL queries). Users need visibility into the full dbt log, especially when debugging failures. This must work even when the component throws an exception.

## Solution

### New Config Option

Add `showDbtLog` (boolean, default `false`) to the component configuration. This option coexists independently with the existing `showExecutedSqls` option — both can be enabled simultaneously.

**Config path:** `parameters.showDbtLog`

### New Service: DbtLogService

**File:** `src/Service/DbtLogService.php`

**Responsibilities:**
- Read new lines from `dbt.log` since the last read
- Output each raw JSON line via PSR-3 logger
- Track file byte offset between calls for incremental reads
- Handle missing/empty file gracefully (no-op)

**Interface:**
```php
class DbtLogService
{
    public function __construct(LoggerInterface $logger, string $logFilePath);
    public function log(): void;
}
```

**Internal state:**
- `int $offset = 0` — byte position in the file, updated after each `log()` call

**Behavior of `log()`:**
1. If file does not exist, return (no-op)
2. If file size < `$offset`, reset `$offset` to 0 (handles file truncation/recreation between steps)
3. Open file, seek to `$offset`. If `fopen` fails, treat as missing file (no-op).
4. Read remaining content line by line
5. For each non-empty line, call `$this->logger->info($line)`
6. Update `$offset` to current file position
7. Close file

**Design note:** Lines are output as raw JSON (NDJSON). This was a deliberate choice — the raw format preserves all dbt metadata and is useful for machine parsing and debugging. The `finally` block provides best-effort log capture; some trailing lines may be lost if dbt crashes without flushing its buffers.

### Component Integration

**In `Component::execute()`:**

Pseudo-code showing the modified execute flow:

```php
// ... existing setup: clone repo, create provider, prepare profiles ...

$dbtLogService = new DbtLogService($this->getLogger(), $projectPath . '/logs/dbt.log');

// dbt deps is prepended to executeSteps before this point
try {
    foreach ($executeSteps as $step) {
        $this->executeStep($step, ...);

        if ($this->getConfig()->getShowDbtLog()) {
            $dbtLogService->log();
        }
    }
} finally {
    if ($this->getConfig()->getShowDbtLog()) {
        $dbtLogService->log();  // capture lines from failed step
    }
}

// ... existing post-loop logic: showExecutedSqls, output manifest ...
```

The `try/finally` wraps only the step loop. Post-loop logic (`showExecutedSqls`, output manifest) remains outside and runs normally on success. On failure, the `finally` block ensures dbt.log lines from the failing step are captured before the exception propagates.

### Config Changes

**ConfigDefinition:** Add `showDbtLog` boolean node (default `false`) alongside `showExecutedSqls`.

**Config:** Add `getShowDbtLog(): bool` getter method.

## Testing

### Unit Tests for DbtLogService

1. **Reads all lines** — write sample NDJSON to a temp file, call `log()`, verify all lines logged
2. **Incremental reads** — write lines, call `log()`, write more lines, call `log()` again, verify only new lines logged on second call
3. **Missing file** — call `log()` with non-existent path, no error thrown
4. **Empty file** — call `log()` on empty file, no lines logged
5. **Multi-step simulation** — append lines between multiple `log()` calls, verify correct incremental output
6. **File truncation** — write lines, call `log()`, truncate/recreate file with new content, call `log()` again, verify offset resets and new content is logged

### Config Tests

- Verify `showDbtLog` defaults to `false`
- Verify `showDbtLog` is accepted as valid config when set to `true`

## Files to Create/Modify

- **Create:** `src/Service/DbtLogService.php`
- **Create:** `tests/phpunit/Service/DbtLogServiceTest.php`
- **Modify:** `src/Configuration/ConfigDefinition.php` — add `showDbtLog` node
- **Modify:** `src/Config.php` — add `getShowDbtLog()` getter
- **Modify:** `src/Component.php` — instantiate DbtLogService, add calls in execute loop and finally block
