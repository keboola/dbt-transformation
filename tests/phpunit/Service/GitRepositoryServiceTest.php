<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use DbtTransformation\Service\GitRepositoryService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GitRepositoryServiceTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../../../data';

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in($this->dataDir));
    }

    public function testListBranches(): void
    {
        $gitService = new GitRepositoryService($this->dataDir);
        $gitService->clone('https://github.com/keboola/dbt-test-project-public.git');
        $branches = $gitService->listRemoteBranches($this->dataDir . '/dbt-project');

        self::assertIsArray($branches);
        self::assertNotEmpty($branches);
        self::assertContains('origin/main', $branches);
        self::assertContains('origin/branch-with-postgres-sources', $branches);
        self::assertContains('origin/branch-with-redshift-sources', $branches);
        self::assertContains('origin/branch-with-bigquery-sources', $branches);
    }
}
