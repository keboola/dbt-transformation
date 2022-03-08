<?php

declare(strict_types=1);

namespace DbtTransformation\Tests;

use DbtTransformation\Config;
use DbtTransformation\ConfigDefinition;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigTest extends TestCase
{
    /**
     * @param array<string, mixed> $configData
     * @dataProvider validConfigsData
     */
    public function testValidConfig(array $configData): void
    {
        $config = new Config($configData, new ConfigDefinition());
        $configData = $this->addDefaultValues($configData);
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
    public function validConfigsData(): Generator
    {
        yield 'minimal config' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'sourceName' => 'my_source',
                    ],
                ],
            ],
        ];

        yield 'config with branch' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'branch' => 'master',
                    ],
                    'dbt' => [
                        'sourceName' => 'my_source',
                    ],
                ],
            ],
        ];

        yield 'config with credentials' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'username' => 'test',
                        'password' => 'test',
                    ],
                    'dbt' => [
                        'sourceName' => 'my_source',
                    ],
                ],
            ],
        ];

        yield 'config with branch and credentials' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'branch' => 'master',
                        'username' => 'test',
                        'password' => 'test',
                    ],
                    'dbt' => [
                        'sourceName' => 'my_source',
                    ],
                ],
            ],
        ];

        yield 'config with show executed SQLs parameter' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'sourceName' => 'my_source',
                    ],
                    'showExecutedSqls' => true,
                ],
            ],
        ];

        yield 'config with model names' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'branch' => 'master',
                        'username' => 'test',
                        'password' => 'test',
                    ],
                    'dbt' => [
                        'sourceName' => 'my_source',
                        'modelNames' => ['stg_model'],
                    ],
                ],
            ],
        ];
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

        yield 'git repo has username but not password' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'username' => 'test',
                    ],
                ],
            ],
            'expectedError' => 'Both username and password has to be set.',
        ];

        yield 'git repo has password but not username' => [
            'configData' => [
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'password' => 'test',
                    ],
                ],
            ],
            'expectedError' => 'Both username and password has to be set.',
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

    /**
     * @param array<string, mixed> $configData
     * @return array<string, mixed>
     */
    protected function addDefaultValues(array $configData): array
    {
        if (!array_key_exists('showExecutedSqls', $configData['parameters'])) {
            $configData['parameters']['showExecutedSqls'] = false;
        }

        if (empty($configData['parameters']['dbt']['modelNames'])) {
            $configData['parameters']['dbt']['modelNames'] = [];
        }

        return $configData;
    }
}
