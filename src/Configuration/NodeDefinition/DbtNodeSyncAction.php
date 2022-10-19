<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\NodeDefinition;

use DbtTransformation\Service\DbtService;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

class DbtNodeSyncAction extends ArrayNodeDefinition
{
    public const NODE_NAME = 'dbt';

    private const ACCEPTED_DBT_COMMANDS = [
        DbtService::COMMAND_RUN,
        DbtService::COMMAND_DOCS_GENERATE,
        DbtService::COMMAND_TEST,
        DbtService::COMMAND_SOURCE_FRESHNESS,
        DbtService::COMMAND_DEBUG,
        DbtService::COMMAND_COMPILE,
        DbtService::COMMAND_SEED,
    ];

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
                ->scalarNode('threads')
                    ->defaultValue(4)
                ->end()
            ->end();
        // @formatter:on
    }
}
