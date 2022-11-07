<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration;

use DbtTransformation\Configuration\NodeDefinition\DbtNode;
use DbtTransformation\Configuration\NodeDefinition\GitNode;
use DbtTransformation\Configuration\NodeDefinition\RemoteDwhNode;
use DbtTransformation\Configuration\NodeDefinition\StorageInputNode;
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
            ->isRequired()
            ->children()
                ->append(new GitNode())
                ->append(new RemoteDwhNode())
                ->append(new DbtNode())
                ->append(new StorageInputNode())
            ->end();

        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
            ->booleanNode('showExecutedSqls')
            ->defaultFalse()
            ->end();

        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
            ->booleanNode('generateSources')
            ->defaultTrue()
            ->end();

        // @formatter:on
        return $parametersNode;
    }
}
