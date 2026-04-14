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

        /** @var array{config: array<string, mixed>, default: array{outputs: array{kbc_prod: array<string, mixed>}}} $masked */
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

        /** @var array{outputs: array{kbc_prod: array<string, mixed>}} $masked */
        $masked = ProfilesHelper::maskSensitiveValues($data);

        self::assertSame('****', $masked['outputs']['kbc_prod']['private_key']);
    }

    public function testMasksAllSensitiveKeys(): void
    {
        $data = [
            'password' => 'secret1',
            'private_key' => 'secret2',
            'private_key_passphrase' => 'secret3',
            'keyfile' => 'secret4',
            'keyfile_json' => 'secret5',
            'key_content' => 'secret6',
            'token' => 'secret7',
            'secret' => 'secret8',
            'client_secret' => 'secret9',
            'account' => 'not-secret',
        ];

        $masked = ProfilesHelper::maskSensitiveValues($data);

        self::assertSame('****', $masked['password']);
        self::assertSame('****', $masked['private_key']);
        self::assertSame('****', $masked['private_key_passphrase']);
        self::assertSame('****', $masked['keyfile']);
        self::assertSame('****', $masked['keyfile_json']);
        self::assertSame('****', $masked['key_content']);
        self::assertSame('****', $masked['token']);
        self::assertSame('****', $masked['secret']);
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

        /** @var array{outputs: array{prod: array<string, mixed>, dev: array<string, mixed>}} $masked */
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
            'host' => 'my-account.snowflakecomputing.com',
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
                    'host' => 'my-account.snowflakecomputing.com',
                ],
            ],
        ];

        /** @var array{outputs: array{kbc_prod: array<string, mixed>}} $resolved */
        $resolved = ProfilesHelper::resolveEnvVars($data);

        self::assertSame('snowflake', $resolved['outputs']['kbc_prod']['type']);
        self::assertSame('secret', $resolved['outputs']['kbc_prod']['password']);
        self::assertSame('my-account.snowflakecomputing.com', $resolved['outputs']['kbc_prod']['host']);

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

    public function testResolvesAsBoolFilter(): void
    {
        putenv('DBT_KBC_PROD_TRUST_CERT=true');

        $data = [
            'trust_cert' => '{{ env_var("DBT_KBC_PROD_TRUST_CERT")| as_bool }}',
        ];

        $resolved = ProfilesHelper::resolveEnvVars($data);

        self::assertTrue($resolved['trust_cert']);

        putenv('DBT_KBC_PROD_TRUST_CERT');
    }

    public function testResolvesAsBoolFilterFalse(): void
    {
        putenv('DBT_KBC_PROD_TRUST_CERT=false');

        $data = [
            'trust_cert' => '{{ env_var("DBT_KBC_PROD_TRUST_CERT")| as_bool }}',
        ];

        $resolved = ProfilesHelper::resolveEnvVars($data);

        self::assertFalse($resolved['trust_cert']);

        putenv('DBT_KBC_PROD_TRUST_CERT');
    }

    public function testAsNumberWithFloatValue(): void
    {
        putenv('DBT_KBC_PROD_TIMEOUT=4.5');

        $data = [
            'timeout' => '{{ env_var("DBT_KBC_PROD_TIMEOUT")| as_number }}',
        ];

        $resolved = ProfilesHelper::resolveEnvVars($data);

        self::assertSame(4.5, $resolved['timeout']);

        putenv('DBT_KBC_PROD_TIMEOUT');
    }

    public function testAsNumberWithNonNumericValueReturnsRawString(): void
    {
        putenv('DBT_KBC_PROD_THREADS=abc');

        $data = [
            'threads' => '{{ env_var("DBT_KBC_PROD_THREADS")| as_number }}',
        ];

        $resolved = ProfilesHelper::resolveEnvVars($data);

        self::assertSame('abc', $resolved['threads']);

        putenv('DBT_KBC_PROD_THREADS');
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
