<?php

declare(strict_types=1);

namespace DbtTransformation\Service;

use DbtTransformation\DwhProvider\DwhLocationEnum;
use DbtTransformation\Helper\ParseDbtOutputHelper;
use Keboola\Component\UserException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DbtService
{
    private const DISALLOWED_OPTIONS_LOCAL_DWH = [
        '--profiles-dir',
        '--log-format',
        '--target',
        '-t',
    ];

    private const DISALLOWED_OPTIONS_REMOTE_DWH = [
        '--profiles-dir',
        '--log-format',
    ];

    public const COMMAND_COMPILE = 'dbt compile';
    public const COMMAND_DOCS_GENERATE = 'dbt docs generate';
    public const COMMAND_DEBUG = 'dbt debug';
    public const COMMAND_RUN = 'dbt run';
    public const COMMAND_DEPS = 'dbt deps';

    private string $projectPath;

    public function __construct(string $projectPath)
    {
        $this->projectPath = $projectPath;
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function runCommand(string $command, DwhLocationEnum $dwhLocation, string $target = 'kbc_prod'): string
    {
        try {
            $command = $this->prepareCommand($command, $dwhLocation, $target);
            $process = new Process($command, $this->projectPath, getenv(), null, null);
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            $output = $e->getProcess()->getOutput();
            if ($output === '') {
                throw new UserException($e->getProcess()->getErrorOutput());
            }
            $logs = iterator_to_array(ParseDbtOutputHelper::getMessagesFromOutput($output, 'error'));
            if (empty($logs)) {
                $logs = iterator_to_array(ParseDbtOutputHelper::getMessagesFromOutput($output));
            }
            throw new UserException(implode(PHP_EOL, $logs));
        }
    }

    /**
     * @return array<int, string>
     * @throws \Keboola\Component\UserException
     */
    protected function prepareCommand(string $command, DwhLocationEnum $dwhLocation, string $target): array
    {
        return [
            'dbt',
            '--log-format',
            'json',
            '--no-use-colors',
            ...$this->getCommandWithoutDbt($command, $dwhLocation),
            '-t',
            $target,
            '--profiles-dir',
            $this->projectPath,
        ];
    }

    /**
     * @return array<string>
     * @throws \Keboola\Component\UserException
     */
    protected function getCommandWithoutDbt(string $commandString, DwhLocationEnum $dwhLocation): array
    {
        $stringInput = new StringInput($commandString);
        $command = $stringInput->getRawTokens(true);
        foreach ($command as $commandPart) {
            $foundOption = $this->findDisallowedOption($commandPart, $dwhLocation);
            if ($foundOption !== null) {
                throw new UserException("You cannot override option {$foundOption} in your dbt command. " .
                    'Please remove it.');
            }
        }

        return $command;
    }

    private function findDisallowedOption(string $commandPart, DwhLocationEnum $dwhLocation): ?string
    {
        $disallowedOptions = match ($dwhLocation) {
            DwhLocationEnum::LOCAL => self::DISALLOWED_OPTIONS_LOCAL_DWH,
            DwhLocationEnum::REMOTE => self::DISALLOWED_OPTIONS_REMOTE_DWH,
        };

        foreach ($disallowedOptions as $option) {
            if (str_starts_with($commandPart, $option) !== false) {
                return $option;
            }
        }
        return null;
    }
}
