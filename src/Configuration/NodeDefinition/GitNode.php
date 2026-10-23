<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\NodeDefinition;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class GitNode extends ArrayNodeDefinition
{
    public const NODE_NAME = 'git';
    public const AUTH_TYPE_ORGANIZATION = 'organization';

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
                if (($item['authType'] ?? null) === self::AUTH_TYPE_ORGANIZATION) {
                    $this->validateOrganizationCredentials($item);
                }
                return $item;
            })
            ->end();
        // @formatter:on
    }

    /**
     * @param array<string, string> $item
     */
    private function validateOrganizationCredentials(array $item): void
    {
        if (!empty($item['username']) || !empty($item['#password'])) {
            throw new InvalidConfigurationException(
                'Organization GitHub authentication cannot be used together with username and password.',
            );
        }

        $url = parse_url($item['repo']);
        if (!$url || ($url['scheme'] ?? null) !== 'https' || isset($url['user']) || isset($url['pass'])) {
            throw new InvalidConfigurationException(
                'Organization GitHub authentication requires an HTTPS URL without credentials.',
            );
        }
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
                ->enumNode('authType')
                    ->values([self::AUTH_TYPE_ORGANIZATION, null])
                ->end()
                ->scalarNode('username')
                ->end()
                ->scalarNode('#password')
                ->end()
                ->scalarNode('folder')
                ->end()
            ->end();
        // @formatter:on
    }
}
