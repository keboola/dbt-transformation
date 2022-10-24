<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\NodeDefinition;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class StorageInputNode extends ArrayNodeDefinition
{
    public const NODE_NAME = 'storage';

    public function __construct(?NodeParentInterface $parent = null)
    {
        parent::__construct(self::NODE_NAME, $parent);

        $this->ignoreExtraKeys();
        $this->build();
    }

    protected function build(): void
    {
        // @formatter:off
        $this
            ->children()
            ->arrayNode('input')
                ->isRequired()
                ->children()
                ->arrayNode('tables')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                        ->scalarNode('source')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('destination')
                        ->end()
                ->end()
            ->end()
        ->end();
        // @formatter:on
    }
}
