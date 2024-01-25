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
        return $this->getStringValue(['parameters', 'git', 'repo']);
    }

    public function getGitRepositoryBranch(): ?string
    {
        try {
            return $this->getStringValue(['parameters', 'git', 'branch']);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function getGitRepositoryUsername(): ?string
    {
        try {
            return $this->getStringValue(['parameters', 'git', 'username']);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function getGitRepositoryPassword(): ?string
    {
        try {
            return $this->getStringValue(['parameters', 'git', '#password']);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    public function showSqls(): bool
    {
        return (bool) $this->getValue(['parameters', 'showExecutedSqls']);
    }

    public function generateSources(): bool
    {
        return (bool) $this->getValue(['parameters', 'generateSources']);
    }

    /**
     * @return array<string>
     */
    private function getModelNames(): array
    {
        return $this->getArrayValue(['parameters', 'dbt', 'modelNames']);
    }

    public function getThreads(): int
    {
        return $this->getIntValue(['parameters', 'dbt', 'threads']);
    }

    /**
     * @return array<int, string>
     */
    public function getExecuteSteps(): array
    {
        $executionSteps = $this->getArrayValue(['parameters', 'dbt', 'executeSteps']);
        $executionSteps = $this->filterActiveSteps($executionSteps);

        // For backward compatibility when modelNames were in the UI
        if (!empty($this->getModelNames())) {
            foreach ($executionSteps as $key => $executionStep) {
                if (!str_contains($executionStep, 'dbt deps')
                    && !str_contains($executionStep, 'dbt debug')
                    && !str_contains($executionStep, 'dbt source freshness')
                    && !str_contains($executionStep, '--select')
                ) {
                    $executionSteps[$key] .= ' --select ' . implode(' ', $this->getModelNames());
                }
            }
        }

        return $executionSteps;
    }

    /**
     * @return array<string, array{period: string, count: int}>
     */
    public function getFreshness(): array
    {
        try {
            $freshness = $this->getArrayValue(['parameters', 'dbt', 'freshness']);
        } catch (InvalidArgumentException $e) {
            return [];
        }

        foreach ($freshness as $key => $value) {
            if ($value['active']) {
                unset($freshness[$key]['active']);
            } else {
                unset($freshness[$key]);
            }
        }

        return $freshness;
    }

    /**
     * @return string[]
     */
    public function getStorageInputTables(): array
    {
        try {
            return array_column($this->getArrayValue(['parameters', 'storage', 'input', 'tables']), 'source');
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }

    /**
     * @return array<string, string>
     */
    public function getRemoteDwh(): array
    {
        return $this->getArrayValue(['parameters', 'remoteDwh']);
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

    /**
     * @return array<string, string>
     */
    public function getWorkspaceCredentials(): array
    {
        return $this->getArrayValue(['authorization', 'workspace']);
    }

    /**
     * @return array<string, bool>
     */
    public function getArtifactsOptions(): array
    {
        return $this->getArrayValue(['artifacts', 'options'], []);
    }

    /**
     * @param array<int, array{'step': string, 'active': bool}> $executionSteps
     * @return array<int, string>
     */
    protected function filterActiveSteps(array $executionSteps): array
    {
        return array_map(function ($step) {
            return $step['step'];
        }, array_filter($executionSteps, function ($step) {
            return $step['active'];
        }));
    }
}
