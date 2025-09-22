<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\NodeDefinition;

use DbtTransformation\DwhProvider\DwhProviderFactory;
use DbtTransformation\DwhProvider\RemoteBigQueryProvider;
use DbtTransformation\DwhProvider\RemoteMssqlProvider;
use DbtTransformation\DwhProvider\RemotePostgresProvider;
use DbtTransformation\DwhProvider\RemoteRedshiftProvider;
use DbtTransformation\DwhProvider\RemoteSnowflakeProvider;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class RemoteDwhNode extends ArrayNodeDefinition
{
    public const NODE_NAME = 'remoteDwh';

    public function __construct(?NodeParentInterface $parent = null)
    {
        parent::__construct(self::NODE_NAME, $parent);

        $this->validateCredentials();
        $this->build();
    }

    protected function validateCredentials(): void
    {
        // @formatter:off
        $this
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
            ->end();

        // For Snowflake, ensure we have either password or private key
        $this
            ->validate()
            ->ifTrue(function ($v) {
                if (!is_array($v)) {
                    return false;
                }
                if (($v['type'] ?? null) !== RemoteSnowflakeProvider::DWH_PROVIDER_TYPE) {
                    return false;
                }
                $hasPassword = array_key_exists('#password', $v) && !empty($v['#password']);
                $hasPrivateKey = array_key_exists('#private_key', $v) && !empty($v['#private_key']);
                return !$hasPassword && !$hasPrivateKey;
            })
            ->thenInvalid('Snowflake requires either "#password" or "#private_key".')
            ->end();
        // @formatter:on
    }

    protected function build(): void
    {
        // @formatter:off
        $this
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
                ->scalarNode('location')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('threads')
                    ->defaultValue(4)
                ->end()
                ->scalarNode('#key_content')
                    ->cannotBeEmpty()
                ->end()
                // Snowflake key-pair auth
                ->scalarNode('#private_key')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('#private_key_passphrase')
                    ->cannotBeEmpty()
                ->end()
            ->end();
        // @formatter:on
    }

    /**
     * @return array<int, string>
     */
    private function getRemoteDwhConnectionParams(string $type): array
    {
        switch ($type) {
            case RemoteSnowflakeProvider::DWH_PROVIDER_TYPE:
                return RemoteSnowflakeProvider::getRequiredConnectionParams();

            case RemotePostgresProvider::DWH_PROVIDER_TYPE:
                return RemotePostgresProvider::getRequiredConnectionParams();

            case RemoteBigQueryProvider::DWH_PROVIDER_TYPE:
                return RemoteBigQueryProvider::getRequiredConnectionParams();

            case RemoteMssqlProvider::DWH_PROVIDER_TYPE:
                return RemoteMssqlProvider::getRequiredConnectionParams();

            case RemoteRedshiftProvider::DWH_PROVIDER_TYPE:
                return RemoteRedshiftProvider::getRequiredConnectionParams();
        }

        throw new InvalidConfigurationException(sprintf('Remote DWH type "%s" not supported', $type));
    }
}
