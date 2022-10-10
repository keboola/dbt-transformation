<?php

declare(strict_types=1);

namespace DbtTransformation;

use InvalidArgumentException;
use Keboola\Component\Config\BaseConfig;
use RuntimeException;

class Config extends BaseConfig
{
    public function getGitRepositoryUrl(): string
    {
        return $this->getValue(['parameters', 'git', 'repo']);
    }

    public function getGitRepositoryBranch(): ?string
    {
        try {
            return $this->getValue(['parameters', 'git', 'branch']);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function getGitRepositoryUsername(): ?string
    {
        try {
            return $this->getValue(['parameters', 'git', 'username']);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function getGitRepositoryPassword(): ?string
    {
        try {
            return $this->getValue(['parameters', 'git', '#password']);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function showSqls(): bool
    {
        return $this->getValue(['parameters', 'showExecutedSqls']);
    }

    /**
     * @return array<string>
     */
    public function getModelNames(): array
    {
        return $this->getValue(['parameters', 'dbt', 'modelNames']);
    }

    public function getThreads(): int
    {
        return $this->getIntValue(['parameters', 'dbt', 'threads']);
    }

    /**
     * @return array<string>
     */
    public function getExecuteSteps(): array
    {
        return $this->getValue(['parameters', 'dbt', 'executeSteps']);
    }

    /**
     * @return array<string, <array{period: string, count: int}>
     */
    public function getFreshness(): array
    {
        try {
            return $this->getValue(['parameters', 'dbt', 'freshness']);
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }

    /**
     * @return array<string, string>
     */
    public function getRemoteDwh(): array
    {
        return $this->getValue(['parameters', 'remoteDwh']);
    }

    public function hasRemoteDwh(): bool
    {
        try {
            $this->getRemoteDwh();
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    public function getConfigId(): string
    {
        return $this->getStringValue(['parameters', 'configId']);
    }

    public function getBranchId(): string
    {
        return $this->getStringValue(['parameters', 'branchId'], 'default');
    }

    public function getStorageApiToken(): string
    {
        $token = getenv('KBC_TOKEN');
        if (!$token) {
            throw new RuntimeException('KBC_TOKEN environment variable must be set');
        }

        return $token;
    }

    public function getStorageApiUrl(): string
    {
        $url = getenv('KBC_URL');
        if (!$url) {
            throw new RuntimeException('KBC_URL environment variable must be set');
        }

        return $url;
    }
}
