<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$environments = [
    'SNOWFLAKE_HOST',
    'SNOWFLAKE_WAREHOUSE',
    'SNOWFLAKE_DATABASE',
    'SNOWFLAKE_SCHEMA',
    'SNOWFLAKE_USER',
    'SNOWFLAKE_PASSWORD',
    'GITHUB_USERNAME',
    'GITHUB_PASSWORD',
    'GITLAB_USERNAME',
    'GITLAB_PASSWORD',
    'BITBUCKET_USERNAME',
    'BITBUCKET_PASSWORD',
];

foreach ($environments as $environment) {
    if (empty(getenv($environment))) {
        throw new RuntimeException(sprintf('Missing environment "%s".', $environment));
    }
}
