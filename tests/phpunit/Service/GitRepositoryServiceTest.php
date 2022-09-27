<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use DbtTransformation\Service\GitRepositoryService;
use Generator;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Throwable;

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
        self::assertContains('main', $branches);
        self::assertContains('branch-with-postgres-sources', $branches);
        self::assertContains('branch-with-redshift-sources', $branches);
        self::assertContains('branch-with-bigquery-sources', $branches);
    }

    /**
     * @dataProvider privateRepositoryValidCredentials
     */
    public function testClonePrivateRepository(string $url, string $username, string $password): void
    {
        try {
            $gitService = new GitRepositoryService($this->dataDir);
            $gitService->clone($url, null, $username, $password);
        } catch (Throwable $exception) {
            $this->fail($exception->getMessage());
        }

        $this->assertTrue(true);
    }

    /**
     * @dataProvider privateRepositoryInvalidCredentials
     */
    public function testClonePrivateRepositoryInvalid(
        string $url,
        string $username,
        string $password,
        string $errorMsg
    ): void {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage($errorMsg);
        $gitService = new GitRepositoryService($this->dataDir);
        $gitService->clone($url, null, $username, $password);
    }

    /**
     * @return Generator<string, array<string, string>>
     */
    public function privateRepositoryValidCredentials(): Generator
    {
        yield 'github private repository' => [
            'url' => 'https://github.com/keboola/dbt-test-project.git',
            'username' => getenv('GITHUB_USERNAME') ?: '',
            'password' => getenv('GITHUB_PASSWORD') ?: '',
        ];

        yield 'gitlab private repository' => [
            'url' => 'https://gitlab.com/keboola/dbt-test-project.git',
            'username' => getenv('GITLAB_USERNAME') ?: '',
            'password' => getenv('GITLAB_PASSWORD') ?: '',
        ];

        yield 'bitbucket private repository' => [
            'url' => 'https://bitbucket.org/dbt-test-project-user/dbt-test-project.git',
            'username' => getenv('BITBUCKET_USERNAME') ?: '',
            'password' => getenv('BITBUCKET_PASSWORD') ?: '',
        ];
    }

    /**
     * @return Generator<string, array<string, string>>
     */
    public function privateRepositoryInvalidCredentials(): Generator
    {
        yield 'github private repository wrong url' => [
            'url' => 'https://github.com/keboola/not-exist-repo.git',
            'username' => getenv('GITHUB_USERNAME') ?: '',
            'password' => getenv('GITHUB_PASSWORD') ?: '',
            'errorMsg' => 'Failed to clone your repository "https://github.com/keboola/not-exist-repo.git":'
                . ' Repository not found.',
        ];

        yield 'gitlab private repository wrong password' => [
            'url' => 'https://gitlab.com/keboola/dbt-test-project.git',
            'username' => getenv('GITLAB_USERNAME') ?: '',
            'password' => 'invalid',
            'errorMsg' => 'Failed to clone your repository "https://gitlab.com/keboola/dbt-test-project.git":'
                . ' HTTP Basic: Access denied. The provided password or token is incorrect or your account has'
                . ' 2FA enabled and you must use a personal access token instead of a password. See https://git'
                . 'lab.com/help/topics/git/troubleshooting_git#error-on-git-fetch-http-basic-access-denied',
        ];

        yield 'bitbucket private repository wrong username' => [
            'url' => 'https://bitbucket.org/dbt-test-project-user/dbt-test-project.git',
            'username' => 'dbt-invalid-user',
            'password' =>  getenv('BITBUCKET_PASSWORD') ?: '',
            'errorMsg' => 'Failed to clone your repository "https://bitbucket.org/dbt-test-project-user/'
                . 'dbt-test-project.git": Invalid credentials',
        ];

        yield 'wrong url' => [
            'url' => 'some random string',
            'username' => '',
            'password' => '',
            'errorMsg' => 'Wrong URL format "some random string"',
        ];
    }
}
