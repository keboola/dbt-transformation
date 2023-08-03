<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalSyncActionsTestsNoZip;

use DbtTransformation\Component;
use DbtTransformation\Service\ArtifactsService;
use Keboola\DatadirTests\DatadirTestCase;
use Keboola\StorageApi\Client as StorageClient;
use Keboola\StorageApi\Options\FileUploadOptions;
use Keboola\Temp\Temp;
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

        $temp = new Temp();
        $tmpFolder = $temp->getTmpFolder();
        $artifacts = new ArtifactsService($storageClient, $tmpFolder, [
            'zip' => false,
        ]);

        $projectPath = __DIR__ . '/../phpunit/data';
        $artifacts->writeResults($projectPath, 'dbt run');

        self::assertFileExists($tmpFolder . '/artifacts/out/current/manifest.json');
        self::assertFileExists($tmpFolder . '/artifacts/out/current/run_results.json');
        self::assertFileExists($tmpFolder . '/artifacts/out/current/model_timing.json');

        $storageClient->uploadFile(
            $tmpFolder . '/artifacts/out/current/manifest.json',
            $options
        );
        $storageClient->uploadFile(
            $tmpFolder . '/artifacts/out/current/run_results.json',
            $options
        );
        $storageClient->uploadFile(
            $tmpFolder . '/artifacts/out/current/model_timing.json',
            $options
        );

        sleep(1);
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in(__DIR__ . '/../../data'));
    }
}
