<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration\NodeDefinition;

use DbtTransformation\Configuration\GitAuthTypeEnum;
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
                if (($item['auth'] ?? null) === GitAuthTypeEnum::GITHUB_APP->value) {
                    $this->validateGitHubAppCredentials($item);
                }
                return $item;
            })
            ->end();
        // @formatter:on
    }

    /**
     * @param array<string, string> $item
     */
    private function validateGitHubAppCredentials(array $item): void
    {
        if (!empty($item['username']) || !empty($item['#password'])) {
            throw new InvalidConfigurationException(
                'GitHub App authentication cannot be used together with username and password.',
            );
        }

        $url = parse_url($item['repo']);
        if (!$url || ($url['scheme'] ?? null) !== 'https' || isset($url['user']) || isset($url['pass'])) {
            throw new InvalidConfigurationException(
                'GitHub App authentication requires an HTTPS repository URL without credentials.',
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
                ->enumNode('auth')
                    ->values(array_column(GitAuthTypeEnum::cases(), 'value'))
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
