<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalTests;

use Keboola\SnowflakeDbAdapter\Connection;

class OdbcTestConnection
{
    /**
     * @return array<string, mixed>
     */
    public static function getDbConfigArray(): array
    {
        return [
            'host' => getenv('SNOWFLAKE_HOST'),
            'warehouse' => getenv('SNOWFLAKE_WAREHOUSE'),
            'database' => getenv('SNOWFLAKE_DATABASE'),
            'user' => getenv('SNOWFLAKE_USER'),
            'password' => getenv('SNOWFLAKE_PASSWORD'),
        ];
    }

    /**
     * @throws \Keboola\SnowflakeDbAdapter\Exception\SnowflakeDbAdapterException
     */
    public static function createConnection(): Connection
    {
        $connection = new Connection(self::getDbConfigArray());
        $connection->query(sprintf('USE SCHEMA "%s"', getenv('SNOWFLAKE_SCHEMA')));
        return $connection;
    }
}
