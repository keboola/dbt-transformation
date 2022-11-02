<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\NodeDefinition;

use DbtTransformation\Service\DbtService;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class DbtNode extends ArrayNodeDefinition
{
    public const NODE_NAME = 'dbt';

    private const ACCEPTED_DBT_COMMANDS = [
        DbtService::COMMAND_BUILD,
        DbtService::COMMAND_RUN,
        DbtService::COMMAND_DOCS_GENERATE,
        DbtService::COMMAND_TEST,
        DbtService::COMMAND_SOURCE_FRESHNESS,
        DbtService::COMMAND_DEBUG,
        DbtService::COMMAND_COMPILE,
        DbtService::COMMAND_SEED,
    ];

    private const ACCEPTED_PERIODS = ['minute', 'hour', 'day'];

    public function __construct(?NodeParentInterface $parent = null)
    {
        parent::__construct(self::NODE_NAME, $parent);

        $this->build();
    }

    protected function build(): void
    {
        // @formatter:off
        $this
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
                ->arrayNode('freshness')
                    ->children()
                        ->arrayNode('warn_after')
                            ->children()
                                ->booleanNode('active')
                                    ->isRequired()
                                ->end()
                                ->integerNode('count')
                                    ->isRequired()
                                ->end()
                                ->enumNode('period')
                                    ->isRequired()
                                    ->values(self::ACCEPTED_PERIODS)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('error_after')
                            ->children()
                                ->booleanNode('active')
                                    ->isRequired()
                                ->end()
                                ->integerNode('count')
                                    ->isRequired()
                                ->end()
                                ->enumNode('period')
                                    ->isRequired()
                                    ->values(self::ACCEPTED_PERIODS)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('threads')
                    ->defaultValue(4)
                ->end()
            ->end();
        // @formatter:on
    }
}
