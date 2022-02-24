<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigDefinition extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->isRequired()
            ->children()
                ->arrayNode('git')
                    ->isRequired()
                    ->children()
                        ->scalarNode('repo')
                            ->isRequired()
                            ->cannotBeEmpty()
                    ->end()
                ->end()
                    ->children()
                        ->scalarNode('branch')
                    ->end()
                ->end()
                ->children()
                        ->scalarNode('username')
                    ->end()
                ->end()
                    ->children()
                        ->scalarNode('password')
                    ->end()
                ->end()
                ->validate()
                ->always(function ($item) {
                    if ((empty($item['username']) && !empty($item['password']))
                        || (!empty($item['username']) && empty($item['password']))
                    ) {
                        throw new InvalidConfigurationException('Both username and password has to be set.');
                    }
                    return $item;
                })
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
                ->end()
                    ->children()
                        ->arrayNode('modelNames')
                            ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end();

        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
            ->booleanNode('showExecutedSqls')
            ->defaultFalse()
            ->end();

        // @formatter:on
        return $parametersNode;
    }
}
