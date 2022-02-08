<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\Config\BaseConfig;

class Config extends BaseConfig
{
    public function getGitRepositoryUrl(): string
    {
        return $this->getValue(['parameters', 'git', 'repo']);
    }

    public function getDbtSourceName(): string
    {
        return $this->getValue(['parameters', 'dbt', 'sourceName']);
    }
}
