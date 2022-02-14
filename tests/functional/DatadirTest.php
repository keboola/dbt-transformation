<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalTests;

use Keboola\DatadirTests\DatadirTestCase;
use Keboola\SnowflakeDbAdapter\Connection;
use RuntimeException;

class DatadirTest extends DatadirTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $testProjectDir = $this->getTestFileDir() . '/' . $this->dataName();

        $this->connection = OdbcTestConnection::createConnection();
        $this->removeAllTables();

        // Load setUp.php file - used to init database state
        $setUpPhpFile = $testProjectDir . '/setUp.php';
        if (file_exists($setUpPhpFile)) {
            // Get callback from file and check it
            $initCallback = require $setUpPhpFile;
            if (!is_callable($initCallback)) {
                throw new RuntimeException(sprintf('File "%s" must return callback!', $setUpPhpFile));
            }

            // Invoke callback
            $initCallback($this);
        }
    }

    protected function removeAllTables(): void
    {
        $dropQueries = $this->connection->fetchAll("
            select 'drop table \"' || table_name || '\";' QUERY
            from information_schema.tables
            where  table_schema = current_schema();
        ");

        foreach ($dropQueries as $dropQuery) {
            $this->connection->query($dropQuery['QUERY']);
        }
    }
}
