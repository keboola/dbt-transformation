<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/../../');
$dotenv->safeLoad();
$dotenv->required([
    'SNOWFLAKE_HOST',
    'SNOWFLAKE_WAREHOUSE',
    'SNOWFLAKE_DATABASE',
    'SNOWFLAKE_SCHEMA',
    'SNOWFLAKE_USER',
    'SNOWFLAKE_PASSWORD',
]);
