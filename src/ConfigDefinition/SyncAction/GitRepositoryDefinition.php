<?php

declare(strict_types=1);

namespace DbtTransformation\ConfigDefinition\SyncAction;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class GitRepositoryDefinition extends BaseConfigDefinition
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
                    ->ignoreExtraKeys()
                    ->validate()
                        ->always(function ($item) {
                            if ((empty($item['username']) && !empty($item['#password']))
                                || (!empty($item['username']) && empty($item['#password']))
                            ) {
                                throw new InvalidConfigurationException('Both username and password has to be set.');
                            }
                            return $item;
                        })
                    ->end()
                    ->isRequired()
                    ->children()
                        ->scalarNode('repo')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('branch')
                        ->end()
                        ->scalarNode('username')
                        ->end()
                        ->scalarNode('#password')
                        ->end()
                    ->end()
                ->end()
            ->end();

        // @formatter:on
        return $parametersNode;
    }
}
