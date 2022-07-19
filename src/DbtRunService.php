<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\UserException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DbtRunService
{

    private string $projectPath;

    public function __construct(string $projectPath)
    {
        $this->projectPath = $projectPath;
    }

    /**
     * @param array<string> $modelNames
     * @throws \Keboola\Component\UserException
     */
    public function run(array $modelNames = [], string $target = 'kbc_prod'): string
    {
        try {
            (new Process(['dbt', 'deps'], $this->projectPath))->mustRun();
            $command = $this->prepareCommand($this->getSelectParameter($modelNames), $target);
            $process = new Process($command, $this->projectPath, getenv());
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new UserException($e->getMessage());
        }
    }

    /**
     * @param array<int, string> $modelNames
     * @return array<int, string>
     */
    protected function getSelectParameter(array $modelNames): array
    {
        $selectParameter = [];
        if (!empty($modelNames)) {
            $selectParameter = ['--select', ...$modelNames];
        }

        return $selectParameter;
    }

    /**
     * @param array<int, string> $selectParameter
     * @return array<int, string>
     */
    protected function prepareCommand(array $selectParameter, string $target): array
    {
        return [
            'dbt',
            '--log-format',
            'json',
            '--warn-error',
            'run',
            '-t',
            $target,
            ...$selectParameter,
            '--profiles-dir',
            $this->projectPath,
        ];
    }
}
