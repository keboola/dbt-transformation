<?php

namespace DbtTransformation\RemoteDWH;

use Exception;

class RemoteDWHFactory
{
    public const REMOTE_DWH_TYPE_SNOWFLAKE = 'snowflake';
    public const REMOTE_DWH_TYPE_POSTGRES = 'postgres';
    public const REMOTE_DWH_TYPE_BIGQUERY = 'bigquery';
    public const REMOTE_DWH_TYPE_REDSHIFT = 'redshift';
    public const REMOTE_DWH_ALLOWED_TYPES = [
        self::REMOTE_DWH_TYPE_SNOWFLAKE,
        self::REMOTE_DWH_TYPE_POSTGRES,
        self::REMOTE_DWH_TYPE_BIGQUERY,
        self::REMOTE_DWH_TYPE_REDSHIFT
//        'oracle',
//        'teradata',
//        'mysql',
//        'sqlite',
    ];

    private static function checkType(string $type): void
    {
        if (!in_array($type, self::REMOTE_DWH_ALLOWED_TYPES)) {
            throw new Exception(sprintf(
                'Remote DWH typ "%s" is not allowed. Allowed types are [%s].',
                $type,
                implode(', ', self::REMOTE_DWH_ALLOWED_TYPES)
            ));
        }
    }

    public static function getConnectionParams(string $type): array
    {
        self::checkType($type);

        switch ($type) {
            case self::REMOTE_DWH_TYPE_POSTGRES:
                return [
                    'schema',
                    'dbname',
                    'host',
                    'port',
                    'user',
                    '#password',
                ];
            case self::REMOTE_DWH_TYPE_BIGQUERY:
                return [
                    'type',
                    'method',
                    'project',
                    'dataset',
                    'threads',
                    '#key_content',
                ];
            case self::REMOTE_DWH_TYPE_SNOWFLAKE:
            default:
                return [
                    'schema',
                    'database',
                    'warehouse',
                    'host',
                    'user',
                    '#password',
                ];
        }
    }

    public static function getDbtParams(string $type): array
    {
        self::checkType($type);

        switch ($type) {
            case self::REMOTE_DWH_TYPE_POSTGRES:
                return [
                    'type',
                    'user',
                    'password',
                    'schema',
                    'dbname',
                    'host',
                    'port',
                ];
            case self::REMOTE_DWH_TYPE_BIGQUERY:
                return [
                    'type',
                    'method',
                    'project',
                    'dataset',
                    'threads',
                    'keyfile',
                ];
            case self::REMOTE_DWH_TYPE_SNOWFLAKE:
            default:
                return [
                    'type',
                    'user',
                    'password',
                    'schema',
                    'warehouse',
                    'database',
                    'account',
                ];
        }
    }
}
