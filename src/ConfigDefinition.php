<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ConfigDefinition extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
                ->arrayNode('git')
                    ->children()->
                        scalarNode('repo')->
                            cannotBeEmpty()
                    ->end()
                ->end()
                ->arrayNode('dbt')
                    ->children()->
                        scalarNode('sourceName')->
                            cannotBeEmpty()
                ->end()
            ->end()
        ;
        // @formatter:on
        return $parametersNode;
    }
}
