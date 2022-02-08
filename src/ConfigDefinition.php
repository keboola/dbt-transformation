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
                    ->isRequired()
                    ->children()
                        ->scalarNode('repo')
                            ->isRequired()
                            ->cannotBeEmpty()
                    ->end()
            ->end();

        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
                ->arrayNode('dbt')
                    ->isRequired()
                    ->children()
                        ->scalarNode('sourceName')
                            ->isRequired()
                            ->cannotBeEmpty()
                ->end()
            ->end();

        // @formatter:on
        return $parametersNode;
    }
}
