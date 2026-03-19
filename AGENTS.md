# AGENTS.md

## Project Overview

PHP 8.3+ Keboola component that runs dbt (data build tool) transformations across data warehouses (Snowflake, BigQuery, PostgreSQL, MSSQL, Redshift). Entry point is `src/run.php` → `src/Component.php`.

## Build & Test Commands

```bash
# Full CI pipeline (lint + cs + static analysis + tests)
composer ci

# Individual checks
composer phplint          # PHP syntax check
composer phpcs            # Coding standards (Keboola ruleset)
composer phpcbf           # Auto-fix coding standards
composer phpstan          # Static analysis (level max)
composer tests-phpunit    # Unit tests

# Functional tests (require environment variables / Docker services)
composer tests-datadir
composer tests-datadir-remote-dwh
composer tests-datadir-sync-actions
```

## Code Conventions

- Every file must start with `declare(strict_types=1);`
- Use full PHP 8.3 type hints on all parameters and return types
- Coding standard: `vendor/keboola/coding-standard/src/ruleset.xml` (enforced by phpcs)
- PHPStan level: max
- Use doc-blocks for describing array shapes for PHPStan (e.g., `@return array<string, string>`)
- Minimize usage of `mixed` type in doc-block annotations
- Constructor promotion with `readonly` where appropriate
- Use `match` expressions over `switch` for control flow

## Architecture Patterns

- **Component pattern**: Main class extends `Keboola\Component\BaseComponent`, config extends `BaseConfig`
- **Factory pattern**: `DwhProviderFactory` instantiates warehouse providers via `match` on `DwhConnectionTypeEnum`
- **Interface-based providers**: All DWH providers implement `DwhProviderInterface`
- **Symfony Config**: Node definitions use `ArrayNodeDefinition` fluent builders
- **Exception handling**: Use `Keboola\Component\UserException` for user-facing errors

## Namespace Structure

```
DbtTransformation\              → src/
DbtTransformation\Tests\        → tests/phpunit/
DbtTransformation\FunctionalTests\ → tests/functional/
```

## Test Conventions

- **Every new functionality MUST be covered with tests**
- Unit tests in `tests/phpunit/`, extending `PHPUnit\Framework\TestCase`
- Test classes named `*Test.php` mirroring source structure
- Functional tests use datadir pattern with `setUp.php`/`tearDown.php`
- Environment variables for credentials loaded via `tests/phpunit/bootstrap.php`

## Docker

- Base image: `php:8.3-cli-trixie`
- Includes Python 3.11.9 (compiled from source) for dbt
- Database drivers: Snowflake ODBC, MS SQL ODBC, PostgreSQL ODBC
- Use `docker compose` for local development with service dependencies

## CI

- GitHub Actions workflow at `.github/workflows/push.yml`
- Build matrix tests against multiple dbt versions (1.8.6, 1.10.17, 1.11.2)
- Deploys to ECR on semantic version tags
