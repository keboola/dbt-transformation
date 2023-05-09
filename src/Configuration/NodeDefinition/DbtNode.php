<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\NodeDefinition;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class DbtNode extends ArrayNodeDefinition
{
    public const NODE_NAME = 'dbt';

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
                        ->arrayPrototype()
                            ->children()
                            ->scalarNode('step')
                                ->isRequired()->cannotBeEmpty()
                            ->end()
                            ->booleanNode('active')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                    ->validate()
                    ->always(function ($executeSteps) {
                        if (empty($executeSteps)) {
                            throw new InvalidConfigurationException(
                                'At least one execute step must be defined'
                            );
                        }
                        foreach ($executeSteps as $input) {
                            if (substr($input['step'], 0, 4) !== 'dbt ') {
                                throw new InvalidConfigurationException(
                                    'Invalid execute step: Command must start with "dbt"'
                                );
                            }
                            if (preg_match('/[|&]/', $input['step'])) {
                                throw new InvalidConfigurationException(
                                    'Invalid execute step: Command contains disallowed metacharacters'
                                );
                            }
                        }
                        return $executeSteps;
                    })
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
