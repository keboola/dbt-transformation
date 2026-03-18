<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Helper;

use DbtTransformation\Helper\ProfilesHelper;
use PHPUnit\Framework\TestCase;

class ProfilesHelperTest extends TestCase
{
    public function testMasksSensitiveKeys(): void
    {
        $data = [
            'config' => ['send_anonymous_usage_stats' => false],
            'default' => [
                'target' => 'dev',
                'outputs' => [
                    'kbc_prod' => [
                        'type' => 'snowflake',
                        'account' => 'my-account',
                        'user' => 'my-user',
                        'password' => 'super-secret-password',
                        'database' => 'my-db',
                        'schema' => 'my-schema',
                        'warehouse' => 'my-wh',
                    ],
                ],
            ],
        ];

        $masked = ProfilesHelper::maskSensitiveValues($data);

        self::assertSame('****', $masked['default']['outputs']['kbc_prod']['password']);
        self::assertSame('my-account', $masked['default']['outputs']['kbc_prod']['account']);
        self::assertSame('my-user', $masked['default']['outputs']['kbc_prod']['user']);
        self::assertSame('my-db', $masked['default']['outputs']['kbc_prod']['database']);
    }

    public function testMasksPrivateKey(): void
    {
        $data = [
            'outputs' => [
                'kbc_prod' => [
                    'type' => 'snowflake',
                    'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIE...',
                ],
            ],
        ];

        $masked = ProfilesHelper::maskSensitiveValues($data);

        self::assertSame('****', $masked['outputs']['kbc_prod']['private_key']);
    }

    public function testMasksAllSensitiveKeys(): void
    {
        $data = [
            'password' => 'secret1',
            'private_key' => 'secret2',
            'keyfile' => 'secret3',
            'key_content' => 'secret4',
            'token' => 'secret5',
            'client_secret' => 'secret6',
            'account' => 'not-secret',
        ];

        $masked = ProfilesHelper::maskSensitiveValues($data);

        self::assertSame('****', $masked['password']);
        self::assertSame('****', $masked['private_key']);
        self::assertSame('****', $masked['keyfile']);
        self::assertSame('****', $masked['key_content']);
        self::assertSame('****', $masked['token']);
        self::assertSame('****', $masked['client_secret']);
        self::assertSame('not-secret', $masked['account']);
    }

    public function testMasksMultipleOutputs(): void
    {
        $data = [
            'outputs' => [
                'prod' => [
                    'type' => 'snowflake',
                    'password' => 'secret-prod',
                    'account' => 'prod-account',
                ],
                'dev' => [
                    'type' => 'snowflake',
                    'private_key' => 'secret-dev',
                    'account' => 'dev-account',
                ],
            ],
        ];

        $masked = ProfilesHelper::maskSensitiveValues($data);

        self::assertSame('****', $masked['outputs']['prod']['password']);
        self::assertSame('prod-account', $masked['outputs']['prod']['account']);
        self::assertSame('****', $masked['outputs']['dev']['private_key']);
        self::assertSame('dev-account', $masked['outputs']['dev']['account']);
    }

    public function testPreservesNonSensitiveValues(): void
    {
        $data = [
            'type' => 'snowflake',
            'account' => 'my-account',
            'insecure_mode' => true,
            'threads' => 4,
        ];

        $masked = ProfilesHelper::maskSensitiveValues($data);

        self::assertSame($data, $masked);
    }

    public function testHandlesEmptyArray(): void
    {
        self::assertSame([], ProfilesHelper::maskSensitiveValues([]));
    }

    public function testResolvesEnvVars(): void
    {
        putenv('DBT_KBC_PROD_TYPE=snowflake');
        putenv('DBT_KBC_PROD_ACCOUNT=my-account');
        putenv('DBT_KBC_PROD_THREADS=4');

        $data = [
            'type' => '{{ env_var("DBT_KBC_PROD_TYPE") }}',
            'account' => '{{ env_var("DBT_KBC_PROD_ACCOUNT") }}',
            'threads' => '{{ env_var("DBT_KBC_PROD_THREADS")| as_number }}',
        ];

        $resolved = ProfilesHelper::resolveEnvVars($data);

        self::assertSame('snowflake', $resolved['type']);
        self::assertSame('my-account', $resolved['account']);
        self::assertSame(4, $resolved['threads']);

        putenv('DBT_KBC_PROD_TYPE');
        putenv('DBT_KBC_PROD_ACCOUNT');
        putenv('DBT_KBC_PROD_THREADS');
    }

    public function testResolvesNestedEnvVars(): void
    {
        putenv('DBT_KBC_PROD_TYPE=snowflake');
        putenv('DBT_KBC_PROD_PASSWORD=secret');

        $data = [
            'outputs' => [
                'kbc_prod' => [
                    'type' => '{{ env_var("DBT_KBC_PROD_TYPE") }}',
                    'password' => '{{ env_var("DBT_KBC_PROD_PASSWORD") }}',
                    'insecure_mode' => true,
                ],
            ],
        ];

        $resolved = ProfilesHelper::resolveEnvVars($data);

        self::assertSame('snowflake', $resolved['outputs']['kbc_prod']['type']);
        self::assertSame('secret', $resolved['outputs']['kbc_prod']['password']);
        self::assertTrue($resolved['outputs']['kbc_prod']['insecure_mode']);

        putenv('DBT_KBC_PROD_TYPE');
        putenv('DBT_KBC_PROD_PASSWORD');
    }

    public function testKeepsUnresolvableEnvVars(): void
    {
        $data = [
            'type' => '{{ env_var("DBT_NONEXISTENT_VAR") }}',
        ];

        $resolved = ProfilesHelper::resolveEnvVars($data);

        self::assertSame('{{ env_var("DBT_NONEXISTENT_VAR") }}', $resolved['type']);
    }

    public function testResolveAndMaskCombined(): void
    {
        putenv('DBT_KBC_PROD_TYPE=snowflake');
        putenv('DBT_KBC_PROD_ACCOUNT=my-account');
        putenv('DBT_KBC_PROD_PASSWORD=super-secret');

        $data = [
            'type' => '{{ env_var("DBT_KBC_PROD_TYPE") }}',
            'account' => '{{ env_var("DBT_KBC_PROD_ACCOUNT") }}',
            'password' => '{{ env_var("DBT_KBC_PROD_PASSWORD") }}',
        ];

        $resolved = ProfilesHelper::resolveEnvVars($data);
        $masked = ProfilesHelper::maskSensitiveValues($resolved);

        self::assertSame('snowflake', $masked['type']);
        self::assertSame('my-account', $masked['account']);
        self::assertSame('****', $masked['password']);

        putenv('DBT_KBC_PROD_TYPE');
        putenv('DBT_KBC_PROD_ACCOUNT');
        putenv('DBT_KBC_PROD_PASSWORD');
    }
}
