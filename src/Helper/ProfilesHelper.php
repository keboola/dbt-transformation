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
}
