<?php

declare(strict_types=1);

namespace DbtTransformation;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigDefinition extends BaseConfigDefinition
{
    private const REMOTE_DWH_ALLOWED_TYPES = [
        'snowflake',
        'postgres',
        'redshift',
        'oracle',
        'bigquery',
        'teradata',
        'mysql',
        'sqlite',
    ];

    private const ACCEPTED_DBT_COMMANDS = ['dbt run', 'dbt docs generate', 'dbt test', 'dbt source freshness'];

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
            ->arrayNode('remoteDwh')
                ->children()
                    ->enumNode('type')
                        ->isRequired()
                        ->values(self::REMOTE_DWH_ALLOWED_TYPES)
                    ->end()
                    ->scalarNode('host')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('user')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('#password')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('port')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('dbname')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('schema')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('warehouse')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('database')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('method')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('project')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('dataset')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('threads')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('#key_content')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end();

        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
                ->arrayNode('dbt')
                    ->isRequired()
                    ->children()
                        ->arrayNode('modelNames')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('executeSteps')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->enumPrototype()
                                ->values(self::ACCEPTED_DBT_COMMANDS)
                            ->end()
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
