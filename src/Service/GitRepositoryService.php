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

        $url = $repositoryUrl;
        if ($username && $password) {
            $githubUrl = 'github.com';
            $url = str_replace($githubUrl, sprintf(
                '%s:%s@%s',
                $username,
                $password,
                $githubUrl
            ), $repositoryUrl);
        }

        try {
            $process = new Process(['git', 'clone', ...$branchArgument, $url, 'dbt-project'], $this->dataDir);
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new UserException(sprintf('Failed to clone your repository: %s', $url));
        }
    }

    public function listRemoteBranches(string $projectPath): array
    {
        $args = ['git', 'branch', '-r', '--format', '"%(refname:short)"'];
        $process = new Process($args, $projectPath);
        $process->mustRun();
        $branches = (array) explode("\n", trim($process->getOutput()));

        return array_map(fn ($item) => trim($item, '"'), $branches);
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
