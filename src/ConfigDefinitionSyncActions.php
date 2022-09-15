<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\DwhProvider\DwhProviderFactory;
use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigDefinitionSyncActions extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->isRequired()
                ->children()
                    ->scalarNode('configId')
                        ->isRequired()
                    ->end()
                    ->scalarNode('branchId')
                    ->end()
                ->end()
            ->end();

        // @formatter:on
        return $parametersNode;
    }
}
