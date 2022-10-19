<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\SyncAction;

use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class DbtCompileDefinition extends BaseConfigDefinition
{
    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->isRequired()
            ->children()
                ->scalarNode('configId')
                    ->isRequired()
                ->end()
                ->integerNode('branchId')
                ->end()
            ->end();

        // @formatter:on
        return $parametersNode;
    }
}
