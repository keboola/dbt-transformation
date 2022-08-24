<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\UserException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DbtService
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
            $output = $e->getProcess()->getOutput();
            $logs = iterator_to_array(ParseDbtOutputHelper::getMessagesFromOutput($output, 'error'));
            throw new UserException(implode(PHP_EOL, $logs));
        }
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function deps(): string
    {
        try {
            $process = new Process(['dbt', '--log-format', 'json', '--warn-error', 'deps'], $this->projectPath);
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            $output = $e->getProcess()->getOutput();
            $logs = iterator_to_array(ParseDbtOutputHelper::getMessagesFromOutput($output, 'error'));
            throw new UserException(implode(PHP_EOL, $logs));
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
