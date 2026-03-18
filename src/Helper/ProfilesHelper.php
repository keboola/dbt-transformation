<?php

declare(strict_types=1);

namespace DbtTransformation\Helper;

class ProfilesHelper
{
    private const SENSITIVE_KEYS = [
        'password',
        'private_key',
        'keyfile',
        'key_content',
        'token',
        'client_secret',
    ];

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function maskSensitiveValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::maskSensitiveValues($value);
            } elseif (is_string($key) && in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
                $data[$key] = '****';
            }
        }

        return $data;
    }

    /**
     * Resolves {{ env_var("...") }} references to actual environment variable values.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function resolveEnvVars(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::resolveEnvVars($value);
            } elseif (is_string($value)) {
                $data[$key] = self::resolveEnvVarString($value);
            }
        }

        return $data;
    }

    private static function resolveEnvVarString(string $value): string|int
    {
        $pattern = '/\{\{\s*env_var\(\s*"([^"]+)"\s*\)\s*(?:\|\s*as_number\s*)?\}\}/';

        if (preg_match('/^\{\{\s*env_var\(\s*"([^"]+)"\s*\)\s*\|\s*as_number\s*\}\}$/', $value, $matches)) {
            $envValue = getenv($matches[1]);
            return $envValue !== false ? (int) $envValue : $value;
        }

        return (string) preg_replace_callback($pattern, function (array $matches): string {
            $envValue = getenv($matches[1]);
            return $envValue !== false ? $envValue : $matches[0];
        }, $value);
    }
}
