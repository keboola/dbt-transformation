<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalSyncActionsTests;

use DbtTransformation\Component;
use Keboola\DatadirTests\DatadirTestCase;
use Keboola\StorageApi\Client as StorageClient;
use Keboola\StorageApi\Options\FileUploadOptions;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DatadirTest extends DatadirTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        putenv('KBC_BRANCHID=12345');

        $options = new FileUploadOptions();
        $options->setTags([
            'artifact',
            'branchId-12345',
            'componentId-' . Component::COMPONENT_ID,
            'configId-12345',
            'jobId-123',
        ]);

        $storageClient = new StorageClient([
            'url' => (string) getenv('KBC_URL'),
            'token' => (string) getenv('KBC_TOKEN'),
        ]);
        $storageClient->uploadFile(__DIR__ . '/../phpunit/data/artifacts.tar.gz', $options);

        sleep(1);
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in(__DIR__ . '/../../data'));
    }
}
