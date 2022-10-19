<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\SyncAction;

use DbtTransformation\Configuration\NodeDefinition\DbtNodeSyncAction;
use DbtTransformation\Configuration\NodeDefinition\GitNode;
use DbtTransformation\Configuration\NodeDefinition\RemoteDwhNode;
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
                ->append(new GitNode())
                ->append(new DbtNodeSyncAction())
                ->append(new RemoteDwhNode())
            ->end();

        // @formatter:on
        return $parametersNode;
    }
}
