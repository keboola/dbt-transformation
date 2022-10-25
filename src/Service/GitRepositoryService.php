<?php

declare(strict_types=1);

namespace DbtTransformation\Service;

use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitRepositoryService
{
    private string $dataDir;
    private string $projectPath;

    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
        $this->projectPath = sprintf('%s/dbt-project/', $dataDir);
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function clone(
        string $repositoryUrl,
        ?string $branch = null,
        ?string $username = null,
        ?string $password = null
    ): void {
        $fs = new Filesystem();
        if ($fs->exists($this->projectPath)) {
            $fs->remove($this->projectPath);
        }

        $branchArgument = [];
        if ($branch) {
            $branchArgument = ['-b', $branch];
        }

        $parsedUrl = parse_url($repositoryUrl);
        if (!$parsedUrl || !array_key_exists('host', $parsedUrl)) {
            throw new UserException(sprintf('Wrong URL format "%s"', $repositoryUrl));
        }
        if ($username && $password) {
            $url = str_replace($parsedUrl['host'], sprintf(
                '%s:%s@%s',
                $username,
                $password,
                $parsedUrl['host']
            ), $repositoryUrl);
        } else {
            $url = $repositoryUrl;
        }

        try {
            $process = new Process(['git', 'clone', ...$branchArgument, $url, 'dbt-project'], $this->dataDir);
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $match = preg_match('/remote: (.*)/', $e->getProcess()->getErrorOutput(), $matches);
            throw new UserException(sprintf(
                'Failed to clone your repository "%s"%s',
                $repositoryUrl,
                !$match ? '' : (': ' . $matches[1])
            ));
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function listRemoteBranches(string $projectPath): array
    {
        $args = ['git', 'branch', '-r', '--format', '"%(refname:short),%(contents:subject),%(objectname:short)"'];
        $process = new Process($args, $projectPath);
        $process->mustRun();
        $branches = (array) explode("\n", trim($process->getOutput()));

        return array_map(function ($item) {
            [$branchRaw, $comment, $sha] = explode(',', $item);
            $branch = str_replace('origin/', '', trim($branchRaw, '"'));
            return [
                'branch' => $branch,
                'comment' => $comment,
                'sha' => $sha,
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
}
