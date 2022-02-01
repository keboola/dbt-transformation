<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\BaseComponent;

class Component extends BaseComponent
{
    protected function run(): void
    {
        $path = '/data/in/tables';
        $files = array_diff(scandir($path) ?: [], ['.', '..']);
        echo 'input tables loaded:' . PHP_EOL;
        foreach ($files as $file) {
            echo $file . PHP_EOL;
        }
        echo PHP_EOL . 'dbt --version output:' . PHP_EOL;
        echo shell_exec('dbt --version');
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }
}
