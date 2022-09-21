<?php

declare(strict_types=1);

namespace DbtTransformation\Service;

use DbtTransformation\Helper\ParseDbtOutputHelper;
use Keboola\Component\UserException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DbtService
{
    public const COMMAND_COMPILE = 'dbt compile';
    public const COMMAND_DOCS_GENERATE = 'dbt docs generate';
    public const COMMAND_DEBUG = 'dbt debug';
    public const COMMAND_RUN = 'dbt run';
    public const COMMAND_SOURCE_FRESHNESS = 'dbt source freshness';
    public const COMMAND_TEST = 'dbt test';
    public const STEP_SEED = 'dbt seed';

    private string $projectPath;

    /** @var array<string> */
    private array $modelNames;

    public function __construct(string $projectPath, array $modelNames = [])
    {
        $this->projectPath = $projectPath;
        $this->modelNames = $modelNames;
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function runCommand(string $command, string $target = 'kbc_prod'): string
    {
        try {
            $command = $this->prepareCommand($command, $target);
            $process = new Process($command, $this->projectPath, getenv(), null, null);
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            $output = $e->getProcess()->getOutput();
            if ($output === '') {
                throw new UserException($e->getProcess()->getErrorOutput());
            }
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
     * @return array<int, string>
     */
    protected function prepareCommand(string $command, string $target): array
    {
        return [
            'dbt',
            '--log-format',
            'json',
            '--warn-error',
            '--no-use-colors',
            ...$this->getCommandWithoutDbt($command),
            '-t',
            $target,
            ...$command !== 'dbt deps' ? $this->getSelectParameter($this->modelNames) : [],
            '--profiles-dir',
            $this->projectPath,
        ];
    }

    /**
     * @param array<string> $modelNames
     */
    public function setModelNames(array $modelNames): void
    {
        $this->modelNames = $modelNames;
    }

    /**
     * @return array<string>
     */
    protected function getCommandWithoutDbt(string $command): array
    {
        $command = explode(' ', $command);
        unset($command[0]);

        return $command;
    }
}
