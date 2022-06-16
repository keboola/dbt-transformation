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
            $command = $this->prepareCommand($this->getSelectParameter($modelNames), $target);
            $process = new Process($command, $this->projectPath, getenv());
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new UserException($e->getMessage());
        }
    }

    /**
     * @param array<string> $modelNames
     * @return array<string>
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
     * @param array<string> $selectParameter
     * @return array<string>
     */
    protected function prepareCommand(array $selectParameter, $target): array
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
