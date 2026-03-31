<?php

declare(strict_types=1);

namespace DbtTransformation\Helper;

class ProfilesHelper
{
    private const SENSITIVE_KEYS = [
        'password',
        'private_key',
        'private_key_passphrase',
        'keyfile',
        'keyfile_json',
        'key_content',
        'token',
        'secret',
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

    private static function resolveEnvVarString(string $value): string|int|float|bool
    {
        $envVarPattern = '/\{\{\s*env_var\(\s*"([^"]+)"\s*\)\s*(?:\|\s*(as_number|as_bool)\s*)?\}\}/';

        // Full-value env_var with filter — cast to the appropriate type
        if (preg_match('/^' . substr($envVarPattern, 1, -1) . '$/', $value, $matches)
            && isset($matches[2]) && $matches[2] !== ''
        ) {
            $envValue = getenv($matches[1]);
            if ($envValue === false) {
                return $value;
            }

            return match ($matches[2]) {
                'as_number' => is_numeric($envValue)
                    ? (str_contains($envValue, '.') ? (float) $envValue : (int) $envValue)
                    : $envValue,
                'as_bool' => filter_var($envValue, FILTER_VALIDATE_BOOLEAN),
            };
        }

        // Inline env_var references (possibly embedded in a larger string) — resolve as strings
        return (string) preg_replace_callback($envVarPattern, function (array $matches): string {
            $envValue = getenv($matches[1]);
            return $envValue !== false ? $envValue : $matches[0];
        }, $value);
    }
}
