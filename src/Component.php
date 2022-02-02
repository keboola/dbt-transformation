<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\BaseComponent;

class Component extends BaseComponent
{
    protected function run(): void
    {
        echo var_export($this->getConfig()->getAuthorization(), true);

        echo $this->getConfig()->getGitRepositoryUrl();
    }

    public function getConfig(): Config
    {
        /** @var Config $config */
        $config = parent::getConfig();
        return $config;
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
