<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalRemoteDwhTests;

use Keboola\DatadirTests\DatadirTestCase;
use Keboola\DatadirTests\DatadirTestSpecificationInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DatadirTest extends DatadirTestCase
{
    private const UNSUPPORTED_DBT_VERSIONS_BY_MSSQL_ADAPTER = ['1.7.1', '1.6.8'];

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in(__DIR__ . '/../../data'));
    }

    /**
     * @dataProvider provideDatadirSpecifications
     */
    public function testDatadir(DatadirTestSpecificationInterface $specification): void
    {
        if ($this->dataName() === 'run-action-with-remote-mssql-dwh'
            && in_array($this->getEnv('DBT_VERSION'), self::UNSUPPORTED_DBT_VERSIONS_BY_MSSQL_ADAPTER)
        ) {
            $this->markTestSkipped('DBT 1.7.1 and 1.6.8 are not supported by MSSQL dbt adapter.');
        }

        parent::testDatadir($specification);
    }
}
