<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\UserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CloneRepositoryService
{

    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function clone(
        string $dataDir,
        string $repositoryUrl,
        ?string $branch = null,
        ?string $username = null,
        ?string $password = null
    ): void {
        $fs = new Filesystem();
        $projectPath = sprintf('%s/dbt-project/', $dataDir);
        if ($fs->exists($projectPath)) {
            $fs->remove($projectPath);
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
            $process = new Process(['git', 'clone', ...$branchArgument, $url, 'dbt-project'], $dataDir);
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new UserException(sprintf('Failed to clone your repository: %s', $url));
        }

        $this->log($projectPath, $repositoryUrl);
    }

    protected function log(string $projectPath, string $repositoryUrl): void
    {
        if ($this->logger !== null) {
            $process = new Process(['git', 'branch', '--show-current'], $projectPath);
            $process->mustRun();
            $branch = trim($process->getOutput());
            $process = new Process(['git', 'rev-parse', 'HEAD'], $projectPath);
            $process->mustRun();

            $this->logger->info(sprintf(
                'Successfully cloned repository %s from branch %s (%s)',
                $repositoryUrl,
                $branch,
                trim($process->getOutput())
            ));
        }
    }
}
