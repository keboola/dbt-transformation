<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\BaseComponent;

class Component extends BaseComponent
{
    protected function run(): void
    {
        $path    = __DIR__ . '../data/in/tables';
        $files = array_diff(scandir($path) ?: [], ['.', '..']);
        echo 'input tables loaded:' . PHP_EOL;
        foreach ($files as $file) {
            echo $file . PHP_EOL;
        }
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
