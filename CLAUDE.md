# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Keboola component that runs **dbt (data build tool) transformations** across multiple data warehouses. PHP 8.3+ application that clones a user's dbt Git repository, generates profiles/sources YAML files for the target warehouse, then executes dbt commands.

Two deployment modes:
- **Local DWH** — Snowflake (`keboola.dbt-transformation`) or BigQuery (`keboola.dbt-transformation-local-bigquery`) where Keboola manages the warehouse
- **Remote DWH** — user-provided warehouse (Snowflake, BigQuery, PostgreSQL, MSSQL, Redshift) via `keboola.dbt-transformation-remote`

## Common Commands

```bash
# Build and install
docker compose build
docker compose run --rm dev composer install --no-scripts

# Full CI (validate + lint + phpcs + phpstan + all tests)
composer ci

# Individual checks
composer phplint                           # PHP syntax
composer phpcs                             # Coding standards
composer phpcbf                            # Auto-fix coding standards
composer phpstan                           # Static analysis (level max)

# Tests
composer tests-phpunit                     # Unit tests
composer tests-datadir                     # Functional tests (local DWH)
composer tests-datadir-remote-dwh          # Functional tests (remote DWH)
composer tests-datadir-sync-actions        # Sync action functional tests

# Run a single unit test file
./vendor/bin/phpunit tests/phpunit/ConfigDefinition/ConfigDefinitionTest.php

# Run a single functional test case (each subdirectory is one test case)
./vendor/bin/phpunit tests/functional/run-action/
```

All test commands run inside the `dev` Docker service: `docker compose run --rm dev composer tests-phpunit`

## Architecture

### Core Pattern

`Component` extends `Keboola\Component\BaseComponent` — the standard Keboola component framework. Entry point is `src/run.php` → `Component::run()`.

### DWH Provider System

`DwhProviderFactory::getProvider()` selects the warehouse implementation based on the KBC component ID (local) or `remoteDwh.type` config value (remote). All providers implement `DwhProviderInterface` with two methods:
- `createDbtYamlFiles()` — generates `profiles.yml` and optionally `sources.yml` for dbt
- `getDwhConnectionType()` — returns `LOCAL` or `REMOTE` enum

Local providers also generate dbt source YAML files (`SnowflakeDbtSourcesYaml`, `BigQueryDbtSourcesYaml`). Remote providers only generate profiles.

### Config Validation

`ConfigDefinition` uses Symfony Config's `ArrayNodeDefinition` for tree-based validation. Sub-nodes are organized into `NodeDefinition/` classes: `DbtNode`, `GitNode`, `RemoteDwhNode`, `StorageInputNode`. The `Config` class (extends `BaseConfig`) provides typed getter methods.

### Key Services

- `DbtService` — executes dbt CLI commands via `Symfony\Process`
- `GitRepositoryService` — clones user's dbt project repository
- `ArtifactsService` — downloads/uploads dbt artifacts between runs
- `DbtLogService` — formats and outputs dbt log lines

### Output Manifests

`OutputManifestSnowflake` and `OutputManifestBigQuery` parse dbt's `manifest.json` to generate Keboola output table manifests. These use `DbtManifestParser` to extract table metadata.

## Testing

### Unit Tests (`tests/phpunit/`)

Standard PHPUnit tests organized by source directory: `ConfigDefinition/`, `DwhProvider/`, `FileDumper/`, `Helper/`, `Service/`.

### Functional Tests (datadir pattern)

Each test case is a subdirectory under `tests/functional/` (or `tests/functionalRemoteDWH/`, etc.) containing:
- `source/data/config.json` — input configuration
- `expected-stdout` — expected output (supports PHPUnit wildcards: `%s`, `%S`, `%A`, `%a`, `%w`)
- `expected-code` — expected exit code
- `expected-stderr` — expected error output (optional)
- `setUp.php` / `tearDown.php` — optional setup/cleanup scripts

The `DatadirTest.php` in each test root discovers and runs all subdirectories as individual test cases.

**When modifying log output or adding new output lines, update all affected `expected-stdout` files across test directories.**

## CI/CD

GitHub Actions matrix builds test across 3 dbt versions (1.8.6, 1.10.17, 1.11.2) and 7 component APP_IDs. Tests run sequentially (`max-parallel: 1`) against real Snowflake, BigQuery, and Redshift instances.

Docker images are deployed to ECR on semantic version tags.
