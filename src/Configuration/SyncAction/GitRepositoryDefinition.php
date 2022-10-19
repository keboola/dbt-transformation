<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\SyncAction;

use DbtTransformation\Configuration\NodeDefinition\GitNode;
use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
                ->append(new GitNode())
            ->end();

        // @formatter:on
        return $parametersNode;
    }
}
