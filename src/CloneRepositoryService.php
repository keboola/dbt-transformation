<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\UserException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CloneRepositoryService
{
    /**
     * @throws \Keboola\Component\UserException
     */
    public function clone(
        string $dataDir,
        string $url,
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

        if ($username && $password) {
            $githubUrl = 'github.com';
            $url = str_replace($githubUrl, sprintf(
                '%s:%s@%s',
                $username,
                $password,
                $githubUrl
            ), $url);
        }

        try {
            $process = new Process(['git', 'clone', ...$branchArgument, $url, 'dbt-project'], $dataDir);
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new UserException(sprintf('Failed to clone your repository: %s', $url));
        }
    }
}
