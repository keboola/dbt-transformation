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
2. Open file, seek to `$offset`
3. Read remaining content line by line
4. For each non-empty line, call `$this->logger->info($line)`
5. Update `$offset` to current file position
6. Close file

### Component Integration

**In `Component::execute()`:**
1. Instantiate `DbtLogService` before the execute steps loop, pointing at `{projectPath}/logs/dbt.log`
2. Wrap the step loop in `try/finally`
3. After each `executeStep()` call: if `showDbtLog` is enabled, call `$dbtLogService->log()`
4. In the `finally` block: if `showDbtLog` is enabled, call `$dbtLogService->log()` to capture lines from failed runs

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

### Config Tests

- Verify `showDbtLog` defaults to `false`
- Verify `showDbtLog` is accepted as valid config when set to `true`

## Files to Create/Modify

- **Create:** `src/Service/DbtLogService.php`
- **Create:** `tests/phpunit/Service/DbtLogServiceTest.php`
- **Modify:** `src/Configuration/ConfigDefinition.php` — add `showDbtLog` node
- **Modify:** `src/Config.php` — add `getShowDbtLog()` getter
- **Modify:** `src/Component.php` — instantiate DbtLogService, add calls in execute loop and finally block
