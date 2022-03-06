<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalTests;

use DbtTransformation\Traits\SnowflakeTestQueriesTrait;
use Keboola\DatadirTests\DatadirTestCase;
use RuntimeException;

class DatadirTest extends DatadirTestCase
{
    use SnowflakeTestQueriesTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $testProjectDir = $this->getTestFileDir() . '/' . $this->dataName();

        $this->connection = OdbcTestConnection::createConnection();
        $this->removeAllTablesAndViews();

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
}
