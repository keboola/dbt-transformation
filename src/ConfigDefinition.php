<?php

declare(strict_types=1);

namespace DbtTransformation;

use DbtTransformation\DwhProvider\DwhProviderFactory;
use DbtTransformation\DwhProvider\RemoteBigQueryProvider;
use DbtTransformation\DwhProvider\RemoteMssqlProvider;
use DbtTransformation\DwhProvider\RemotePostgresProvider;
use DbtTransformation\DwhProvider\RemoteRedshiftProvider;
use DbtTransformation\DwhProvider\RemoteSnowflakeProvider;
use Keboola\Component\Config\BaseConfigDefinition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigDefinition extends BaseConfigDefinition
{
    private const ACCEPTED_DBT_COMMANDS = [
        Component::STEP_RUN,
        Component::STEP_DOCS_GENERATE,
        Component::STEP_TEST,
        Component::STEP_SOURCE_FRESHNESS,
    ];

    protected function getParametersDefinition(): ArrayNodeDefinition
    {
        $parametersNode = parent::getParametersDefinition();
        // @formatter:off
        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->isRequired()
            ->children()
                ->arrayNode('git')
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
                        ->scalarNode('password')
                        ->end()
                    ->end()
                ->end()
            ->end();

        /** @noinspection NullPointerExceptionInspection */
        $parametersNode
            ->children()
            ->arrayNode('remoteDwh')
                ->validate()
                    ->ifTrue(function ($v) {
                        if (!is_array($v)) {
                            return true;
                        }
                        $requiredSettings = $this->getRemoteDwhConnectionParams($v['type']);
                        foreach ($requiredSettings as $setting) {
                            if (!array_key_exists($setting, $v)) {
                                return true;
                            }
                        }
                        return false;
                    })
                    ->thenInvalid('Missing required options for "%s"')
                ->end()
                ->children()
                    ->enumNode('type')
                        ->isRequired()
                        ->values(DwhProviderFactory::REMOTE_DWH_ALLOWED_TYPES)
                    ->end()
                    ->scalarNode('host')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('server')
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

    /**
     * @return array<int, string>
     */
    private function getRemoteDwhConnectionParams(string $type): array
    {
        switch ($type) {
            case RemoteSnowflakeProvider::DWH_PROVIDER_TYPE:
                return RemoteSnowflakeProvider::getConnectionParams();

            case RemotePostgresProvider::DWH_PROVIDER_TYPE:
                return RemotePostgresProvider::getConnectionParams();

            case RemoteBigQueryProvider::DWH_PROVIDER_TYPE:
                return RemoteBigQueryProvider::getConnectionParams();

            case RemoteMssqlProvider::DWH_PROVIDER_TYPE:
                return RemoteMssqlProvider::getConnectionParams();

            case RemoteRedshiftProvider::DWH_PROVIDER_TYPE:
                return RemoteRedshiftProvider::getConnectionParams();
        }

        throw new InvalidConfigurationException(sprintf('Remote DWH type "%s" not supported', $type));
    }
}
