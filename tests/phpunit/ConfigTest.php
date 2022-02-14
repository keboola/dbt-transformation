<?php

declare(strict_types=1);

namespace Keboola\DbExtractor\Tests;

use DbtTransformation\Config;
use DbtTransformation\ConfigDefinition;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigTest extends TestCase
{
    public function testValidConfig(): void
    {
        $configData['parameters'] = [
            'git' => [
                'repo' => 'https://github.com/my-repo',
            ],
            'dbt' => [
                'sourceName' => 'my_source',
            ],
        ];

        $config = new Config($configData, new ConfigDefinition());

        $this->assertEquals($configData, $config->getData());
    }

    /**
     * @param array<string, mixed> $configData
     * @dataProvider invalidConfigsData
     */
    public function testInvalidConfigs(array $configData, string $expectedError): void
    {
        try {
            new Config($configData, new ConfigDefinition());
            $this->fail('Validation should produce error');
        } catch (InvalidConfigurationException $e) {
            $this->assertStringContainsString($expectedError, $e->getMessage());
        }
    }

    /**
     * @return Generator<string, array<string, mixed>>
     */
    public function invalidConfigsData(): Generator
    {
        yield 'empty config' => [
            'configData' => [],
            'expectedError' => 'The child config "parameters" under "root" must be configured.',
        ];

        yield 'empty parameters' => [
            'configData' => [
                'parameters' => [],
            ],
            'expectedError' => 'The child config "git" under "root.parameters" must be configured.',
        ];

        yield 'empty git' => [
            'configData' => [
                'parameters' => [
                    'git' => [],
                ],
            ],
            'expectedError' => 'The child config "repo" under "root.parameters.git" must be configured.',
        ];

        yield 'empty git repo' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => '',
                    ],
                ],
            ],
            'expectedError' => 'The path "root.parameters.git.repo" cannot contain an empty value, but got ""',
        ];

        yield 'empty dbt' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [],
                ],
            ],
            'expectedError' => 'The child config "sourceName" under "root.parameters.dbt" must be configured.',
        ];

        yield 'empty sourceName' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'sourceName' => '',
                    ],
                ],
            ],
            'expectedError' => 'The path "root.parameters.dbt.sourceName" cannot contain an empty value, but got ""',
        ];
    }
}
