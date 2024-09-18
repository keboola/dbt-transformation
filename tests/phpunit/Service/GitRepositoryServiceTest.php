<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use ColinODell\PsrTestLogger\TestLogger;
use DbtTransformation\Service\GitRepositoryService;
use Generator;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;
use Retry\BackOff\NoBackOffPolicy;
use Retry\Policy\CallableRetryPolicy;
use Retry\RetryProxy;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
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

    public function testRetryProxy(): void
    {
        $testLogger = new TestLogger();
        $gitService = new GitRepositoryService(
            $this->dataDir,
            new RetryProxy(
                new CallableRetryPolicy(function (ProcessFailedException $e) {
                    return str_contains($e->getMessage(), 'shallow file has changed since we read it');
                }),
                new NoBackOffPolicy(), //no need to make tests slower
                $testLogger,
            ),
        );
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['mustRun', 'getOutput', 'getErrorOutput', 'isStarted'])
            ->getMock();

        $processMock->method('isStarted')->willReturn(true);
        $processMock->method('getOutput')->willReturn('Cloning into \'dbt-project\'...');
        $processMock->method('getErrorOutput')->willReturn('Encountered an error: Internal Error ' .
         'Error checking out spec=\'0.0.2\' for repo https://github.com/keboola/dbt-test-project-public.git' .
         'fatal: shallow file has changed since we read it');

        $exception = new ProcessFailedException($processMock);

        $processMock->method('mustRun')->willThrowException($exception);

        try {
            $gitService->runGitCloneProcess(
                $processMock,
                'https://github.com/keboola/dbt-test-project-public.git',
            );
        } catch (Throwable $e) {
            self::assertSame(UserException::class, get_class($e));
            self::assertEquals(
                'Failed to clone your repository "https://github.com/keboola/dbt-test-project-public.git"',
                $e->getMessage(),
            );
        }

        for ($i = 1; $i < CallableRetryPolicy::DEFAULT_MAX_ATTEMPTS; $i++) {
            self::assertTrue($testLogger->hasInfoThatContains(
                sprintf('shallow file has changed since we read it. Retrying... [%dx]', $i),
            ));
        }
    }

    public function testListBranches(): void
    {
        $gitService = new GitRepositoryService($this->dataDir);
        $gitService->clone('https://github.com/keboola/dbt-test-project-public.git');
        $branches = $gitService->listRemoteBranches($this->dataDir . '/dbt-project');

        self::assertIsArray($branches);
        self::assertNotEmpty($branches);
        $branchNames = array_column($branches, 'branch');
        self::assertContains('main', $branchNames);
        self::assertContains('branch-with-postgres-sources', $branchNames);
        self::assertContains('branch-with-redshift-sources', $branchNames);
        self::assertContains('branch-with-bigquery-sources', $branchNames);

        $mainBranches = array_filter($branches, fn($item) => $item['branch'] === 'main');
        $mainBranch = (array) array_shift($mainBranches);

        self::assertArrayHasKey('comment', $mainBranch);
        self::assertArrayHasKey('sha', $mainBranch);
        self::assertArrayHasKey('author', $mainBranch);
        $author = (array) $mainBranch['author'];
        self::assertArrayHasKey('name', $author);
        self::assertArrayHasKey('email', $author);
        self::assertArrayHasKey('date', $mainBranch);
        self::assertNotEmpty($mainBranch['comment']);
        self::assertNotEmpty($mainBranch['sha']);
        self::assertNotEmpty($author['name']);
        self::assertNotEmpty($author['email']);
        self::assertNotEmpty($mainBranch['date']);
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

    public function testListBranchesEmptyGitProject(): void
    {
        $gitService = new GitRepositoryService($this->dataDir);
        $gitService->clone('https://github.com/keboola/empty-repo-test.git');
        $branches = $gitService->listRemoteBranches($this->dataDir . '/dbt-project');

        self::assertIsArray($branches);
        self::assertEmpty($branches);
    }

    /**
     * @dataProvider getUrlValidParameters
     */
    public function testGetRepositoryUrl(
        string $url,
        ?string $username,
        ?string $password,
        string $expectedUrl,
    ): void {
        $gitService = new GitRepositoryService($this->dataDir);
        self::assertSame($expectedUrl, $gitService->getUrl($url, $username, $password));
    }

    public function testGetInvalidRepositoryUrl(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Wrong URL format "invalid url"');
        $gitService = new GitRepositoryService($this->dataDir);
        $gitService->getUrl('invalid url');
    }

    /**
     * @dataProvider privateRepositoryInvalidCredentials
     */
    public function testClonePrivateRepositoryInvalid(
        string $url,
        string $username,
        string $password,
        string $errorMsg,
    ): void {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage($errorMsg);
        $gitService = new GitRepositoryService($this->dataDir);
        $gitService->clone($url, null, $username, $password);
    }

    public function testCloneWithInvalidFolder(): void
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Folder "invalid-folder" not found in the repository.');
        $gitService = new GitRepositoryService($this->dataDir);
        $gitService->clone(
            repositoryUrl: 'https://github.com/keboola/dbt-test-project-public.git',
            folder: 'invalid-folder',
        );
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
            'password' => getenv('BITBUCKET_PASSWORD') ?: '',
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

    public function getUrlValidParameters(): Generator
    {
        yield 'public repository' => [
            'url' => 'https://github.com/keboola/dbt-test-project-public',
            'username' => null,
            'password' => null,
            'expectedUrl' => 'https://github.com/keboola/dbt-test-project-public',
        ];

        yield 'private repository' => [
            'url' => 'https://github.com/keboola/dbt-test-project.git',
            'username' => getenv('GITHUB_USERNAME') ?: '',
            'password' => getenv('GITHUB_PASSWORD') ?: '',
            'expectedUrl' => sprintf(
                'https://%s:%s@%s',
                getenv('GITHUB_USERNAME') ?: '',
                getenv('GITHUB_PASSWORD') ?: '',
                'github.com/keboola/dbt-test-project.git',
            ),
        ];

        yield 'private repository with special chars' => [
            'url' => 'https://github.com/keboola/dbt-test-project.git',
            'username' => 'user@company.com',
            'password' => '/some@password!',
            'expectedUrl' => 'https://user%40company.com:%2Fsome%40password%21@github.com/keboola/dbt-test-project.git',
        ];
    }
}
