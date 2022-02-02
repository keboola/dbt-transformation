<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\BaseComponent;
use Symfony\Component\Yaml\Yaml;

class Component extends BaseComponent
{
    protected function run(): void
    {
        $yaml = Yaml::dump($this->getConfig()->getAuthorization());
        file_put_contents($this->getDataDir() . '/out/files/profile.yaml', $yaml);

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
