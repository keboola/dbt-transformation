<?php

declare(strict_types=1);

namespace DbtTransformation\Service;

use Keboola\Component\UserException;
use Retry\RetryProxy;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class GitRepositoryService
{
    private const GITHUB_APP_TOKEN_ENV = 'GIT_GITHUB_APP_TOKEN';
    private const GITHUB_APP_TOKEN_USERNAME = 'x-access-token';
    // git calls the helper with "get", "store" or "erase"; only "get" returns the credentials
    private const GITHUB_APP_CREDENTIAL_HELPER
        = '!f() { test "$1" = get || exit 0; echo username=%s; echo "password=$%s"; }; f';

    private string $dataDir;
    private string $projectPath;
    private ?RetryProxy $retryProxy;

    public function __construct(string $dataDir, ?RetryProxy $retryProxy = null)
    {
        $this->dataDir = $dataDir;
        $this->projectPath = sprintf('%s/dbt-project/', $dataDir);
        $this->retryProxy = $retryProxy;
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function clone(
        string $repositoryUrl,
        ?string $branch = null,
        ?string $username = null,
        ?string $password = null,
        ?string $folder = null,
        ?string $gitHubAppToken = null,
    ): void {
        $fs = new Filesystem();
        if ($fs->exists($this->projectPath)) {
            $fs->remove($this->projectPath);
        }

        if ($fs->exists($this->dataDir . '/temp')) {
            $fs->remove($this->dataDir . '/temp');
        }

        $branchArgument = [];
        if ($branch) {
            $branchArgument = ['-b', $branch];
        }

        $gitFolder = $folder === null ? 'dbt-project' : 'temp';

        if ($gitHubAppToken === null) {
            $url = $this->getUrl($repositoryUrl, $username, $password);
            $process = new Process(['git', 'clone', ...$branchArgument, $url, $gitFolder], $this->dataDir);
            $this->runGitCloneProcess($process, $repositoryUrl);
        } else {
            $this->cloneWithGitHubAppToken($repositoryUrl, $branchArgument, $gitFolder, $gitHubAppToken);
        }

        if ($folder !== null) {
            $folderPath = sprintf('%s/temp/%s', $this->dataDir, $folder);
            if ($fs->exists($folderPath) === false) {
                throw new UserException(sprintf('Folder "%s" not found in the repository.', $folder));
            }

            $fs->symlink(
                sprintf('%s/temp/%s', $this->dataDir, $folder),
                sprintf('%s/dbt-project', $this->dataDir),
            );
        }
    }

    /**
     * The token is passed to git only through the environment of the clone process and read back by
     * an inline credential helper, so it never appears in the URL, the command line, .git/config or logs.
     *
     * @param string[] $branchArgument
     * @throws \Keboola\Component\UserException
     */
    private function cloneWithGitHubAppToken(
        string $repositoryUrl,
        array $branchArgument,
        string $gitFolder,
        string $token,
    ): void {
        $credentialHelper = sprintf(
            self::GITHUB_APP_CREDENTIAL_HELPER,
            self::GITHUB_APP_TOKEN_USERNAME,
            self::GITHUB_APP_TOKEN_ENV,
        );
        $process = new Process(
            [
                'git',
                '-c',
                'credential.helper=',
                '-c',
                'credential.helper=' . $credentialHelper,
                'clone',
                ...$branchArgument,
                $repositoryUrl,
                $gitFolder,
            ],
            $this->dataDir,
            [
                'GIT_TERMINAL_PROMPT' => '0',
                self::GITHUB_APP_TOKEN_ENV => $token,
            ],
        );
        $this->runGitCloneProcess(
            $process,
            $repositoryUrl,
            'Check that the GitHub App is installed for this repository and that the installation '
            . 'is not suspended.',
        );
    }

    /**
     * @return array<int, array<string, array<string, string>|string>>
     */
    public function listRemoteBranches(string $projectPath): array
    {
        $args = [
            'git',
            'branch',
            '-r',
            '--format',
            '%(refname:short),%(subject),%(objectname:short),%(authorname),%(authoremail),%(authordate)',
        ];
        $process = new Process($args, $projectPath);
        $process->mustRun();

        if (trim($process->getOutput()) !== '') {
            $branches = (array) explode("\n", trim($process->getOutput()));
        } else {
            return [];
        }

        return array_map(function ($item) {
            [$branchRaw, $comment, $sha, $author, $email, $date] = explode(',', $item);
            $branch = str_replace('origin/', '', $branchRaw);
            return [
                'branch' => $branch,
                'comment' => $comment,
                'sha' => $sha,
                'author' => [
                    'name' => $author,
                    'email' => $email,
                ],
                'date' => $date,
            ];
        }, $branches);
    }

    /**
     * @return array<string, string>
     */
    public function getCurrentBranch(string $projectPath): array
    {
        $process = new Process(['git', 'branch', '--show-current'], $projectPath);
        $process->mustRun();
        $branch = trim($process->getOutput());
        $process = new Process(['git', 'rev-parse', 'HEAD'], $projectPath);
        $process->mustRun();

        return [
            'name' => $branch,
            'ref' => trim($process->getOutput()),
        ];
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function getUrl(string $repositoryUrl, ?string $username = null, ?string $password = null): string
    {
        $parsedUrl = parse_url($repositoryUrl);
        if (!$parsedUrl || !array_key_exists('host', $parsedUrl)) {
            throw new UserException(sprintf('Wrong URL format "%s"', $repositoryUrl));
        }
        if ($username && $password) {
            $url = (string) str_replace($parsedUrl['host'], sprintf(
                '%s:%s@%s',
                urlencode($username),
                urlencode($password),
                $parsedUrl['host'],
            ), $repositoryUrl);
        } else {
            $url = $repositoryUrl;
        }

        return $url;
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function runGitCloneProcess(Process $process, string $repositoryUrl, ?string $failureHint = null): void
    {
        try {
            if ($this->retryProxy) {
                $this->retryProxy->call(function () use ($process): void {
                    $process->mustRun();
                });
            } else {
                $process->mustRun();
            }
        } catch (ProcessTimedOutException $e) {
            throw new UserException(sprintf(
                'Failed to clone your repository "%s": the git clone process timed out after %d seconds. '
                . 'Check that the repository is reachable from Keboola and that it is not too large.',
                $repositoryUrl,
                (int) $e->getExceededTimeout(),
            ));
        } catch (ProcessFailedException $e) {
            $match = preg_match('/remote: (.*)/', $e->getProcess()->getErrorOutput(), $matches);
            $message = sprintf(
                'Failed to clone your repository "%s"%s',
                $repositoryUrl,
                !$match ? '' : (': ' . $matches[1]),
            );
            if ($failureHint !== null) {
                $message = sprintf('%s%s %s', $message, $match ? '' : '.', $failureHint);
            }
            throw new UserException($message);
        }
    }
}
