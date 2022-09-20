<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\ConfigDefinition;

use DbtTransformation\Config;
use DbtTransformation\ConfigDefinition\ConfigDefinition;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigDefinitionTest extends TestCase
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
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt run'],
                    ],
                ],
            ],
        ];

        yield 'config with branch' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'branch' => 'master',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt run'],
                    ],
                ],
            ],
        ];

        yield 'config with credentials' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'username' => 'test',
                        'password' => 'test',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt run'],
                    ],
                ],
            ],
        ];

        yield 'config with branch and credentials' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'branch' => 'master',
                        'username' => 'test',
                        'password' => 'test',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt run'],
                    ],
                ],
            ],
        ];

        yield 'config with show executed SQLs parameter' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt run'],
                    ],
                    'showExecutedSqls' => true,
                ],
            ],
        ];

        yield 'config with model names' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'branch' => 'master',
                        'username' => 'test',
                        'password' => 'test',
                    ],
                    'dbt' => [
                        'modelNames' => ['stg_model'],
                        'executeSteps' => ['dbt run'],
                    ],
                ],
            ],
        ];

        yield 'config with multiple execute steps' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt run', 'dbt docs generate'],
                    ],
                ],
            ],
        ];

        yield 'config with remote DWH postgres' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt run'],
                    ],
                    'remoteDwh' => [
                        'type' => 'postgres',
                        'host' => 'postgres',
                        'user' => 'user',
                        '#password' => 'pass',
                        'port' => '5432',
                        'dbname' => 'db',
                        'schema' => 'schema',
                    ],
                ],
            ],
        ];

        yield 'config with remote DWH bigquery' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt run'],
                    ],
                    'remoteDwh' => [
                        'type' => 'bigquery',
                        'method' => 'service-account',
                        'project' => 'gcp-project',
                        'dataset' => 'dbt',
                        'threads' => '1',
                        '#key_content' => '{"type":"service_account","project_id":"gcp-project",' .
                            '"private_key_id":"1234567"}',
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
            'configData' => [
                'action' => 'run',
            ],
            'expectedError' => 'The child config "parameters" under "root" must be configured.',
        ];

        yield 'empty parameters' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [],
            ],
            'expectedError' => 'The child config "git" under "root.parameters" must be configured.',
        ];

        yield 'empty git' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [],
                ],
            ],
            'expectedError' => 'The child config "repo" under "root.parameters.git" must be configured.',
        ];

        yield 'empty git repo' => [
            'configData' => [
                'action' => 'run',
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
                'action' => 'run',
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
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                        'password' => 'test',
                    ],
                ],
            ],
            'expectedError' => 'Both username and password has to be set.',
        ];

        yield 'missing dbt node' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                ],
            ],
            'expectedError' => 'The child config "dbt" under "root.parameters" must be configured.',
        ];

        yield 'missing execute steps' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                    ],
                ],
            ],
            'expectedError' => 'The child config "executeSteps" under "root.parameters.dbt" must be configured.',
        ];

        yield 'no execute steps' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'executeSteps' => [],
                    ],
                ],
            ],
            'expectedError' => 'The path "root.parameters.dbt.executeSteps" should have at least 1 element(s) defined.',
        ];

        yield 'invalid execute steps' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [
                        'executeSteps' => ['dbt do nothing'],
                    ],
                ],
            ],
            'expectedError' => 'The value "dbt do nothing" is not allowed for path ' .
                '"root.parameters.dbt.executeSteps.0". Permissible values: "dbt run", "dbt docs generate", ' .
                '"dbt test", "dbt source freshness"',
        ];

        yield 'config with remote DWH non-supported type' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [],
                    'remoteDwh' => [
                        'type' => 'elasticsearch',
                        'host' => 'elasticsearch',
                        'user' => 'user',
                        '#password' => 'pass',
                        'port' => '9200',
                    ],
                ],
            ],
            'expectedError' => 'The value "elasticsearch" is not allowed for path "root.parameters.remoteDwh.type". ' .
                'Permissible values: "snowflake", "postgres", "bigquery", "sqlserver", "redshift"',
        ];

        yield 'config with remote DWH missing credentials' => [
            'configData' => [
                'action' => 'run',
                'parameters' => [
                    'git' => [
                        'repo' => 'https://github.com/my-repo',
                    ],
                    'dbt' => [],
                    'remoteDwh' => [
                        'type' => 'postgres',
                        'host' => 'postgres',
                        'user' => '',
                        '#password' => '',
                        'port' => '5432',
                        'dbname' => 'db',
                        'schema' => 'schema',
                    ],
                ],
            ],
            'expectedError' => 'The path "root.parameters.remoteDwh.user" cannot contain an empty value, but got "".',
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
