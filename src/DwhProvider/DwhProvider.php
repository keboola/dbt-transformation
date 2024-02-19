<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

use RuntimeException;

abstract class DwhProvider implements DwhProviderInterface
{
    /**
     * @param array<int, string> $configurationNames
     * @param array<int, string> $dbtParams
     * @param array<int, int> $projectIds
     * @return array<string, array<string, string>>
     */
    public static function getOutputs(array $configurationNames, array $dbtParams, array $projectIds = []): array
    {
        $outputs = [];
        if (empty($configurationNames)) {
            $outputs['kbc_prod'] = self::getOutputDefinition('KBC_PROD', $dbtParams);
            foreach ($projectIds as $projectId) {
                $configurationName = sprintf('kbc_prod_%d', $projectId);
                $outputs[$configurationName] = self::getOutputDefinition(strtoupper($configurationName), $dbtParams);
            }
        }

        foreach ($configurationNames as $configurationName) {
            $outputs[strtolower($configurationName)] = self::getOutputDefinition($configurationName, $dbtParams);
        }

        return $outputs;
    }

    /**
     * @param array<int, string> $dbtParams
     * @return array<string, string>
     */
    protected static function getOutputDefinition(string $configurationName, array $dbtParams): array
    {
        $values = array_map(function ($item) use ($configurationName) {
            $filter = '';
            if ($item === 'threads' || $item === 'port') {
                $filter = '| as_number';
            }
            if ($item === 'trust_cert') {
                $filter = '| as_bool';
            }
            return sprintf('{{ env_var("DBT_%s_%s")%s }}', $configurationName, strtoupper($item), $filter);
        }, $dbtParams);

        $outputDefinition = array_combine($dbtParams, $values);

        if (!$outputDefinition) {
            throw new RuntimeException('Failed to get output definition');
        }

        return $outputDefinition;
    }
}
