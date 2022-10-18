<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\NodeDefinition;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class GitNode extends ArrayNodeDefinition
{
    public const NODE_NAME = 'git';

    public function __construct(?NodeParentInterface $parent = null)
    {
        parent::__construct(self::NODE_NAME, $parent);

        $this->ignoreExtraKeys();
        $this->validateCredentials();
        $this->build();
    }

    protected function validateCredentials(): void
    {
        // @formatter:off
        $this
            ->validate()
            ->always(function ($item) {
                if ((empty($item['username']) && !empty($item['#password']))
                    || (!empty($item['username']) && empty($item['#password']))
                ) {
                    throw new InvalidConfigurationException('Both username and password has to be set.');
                }
                return $item;
            })
            ->end();
        // @formatter:on
    }

    protected function build(): void
    {
        // @formatter:off
        $this
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
            ->end();
        // @formatter:on
    }
}
