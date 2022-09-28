<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use DbtTransformation\Component;
use DbtTransformation\Service\ArtifactsService;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client as StorageClient;
use Keboola\StorageApi\Options\FileUploadOptions;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;

class ArtifactsServiceTest extends TestCase
{
    private StorageClient $storageClient;

    public function setUp(): void
    {
        $this->storageClient = new StorageClient([
            'url' => (string) getenv('KBC_URL'),
            'token' => (string) getenv('KBC_TOKEN'),
        ]);
    }

    public function testDownloadLastRun(): void
    {
        $fileId = $this->uploadTestArtifact();
        sleep(1);

        $temp = new Temp();
        $artifacts = new ArtifactsService($this->storageClient, $temp->getTmpFolder());
        $downloadedFileId = $artifacts->downloadLastRun(Component::COMPONENT_ID, '123', 'default');
        self::assertEquals($fileId, $downloadedFileId);

        $expectedPaths = [
            '/dbt docs generate',
            '/dbt docs generate/manifest.json',
            '/dbt docs generate/run_results.json',
            '/dbt docs generate/catalog.json',
            '/dbt run',
            '/dbt run/manifest.json',
            '/dbt run/run_results.json',
            '/dbt run/graph.gpickle',
            '/dbt source freshness',
            '/dbt source freshness/manifest.json',
            '/dbt source freshness/run_results.json',
            '/dbt source freshness/catalog.json',
            '/dbt source freshness/sources.json',
            '/dbt source freshness/graph.gpickle',
            '/dbt test',
            '/dbt test/manifest.json',
            '/dbt test/run_results.json',
            '/dbt test/partial_parse.msgpack',
            '/dbt test/catalog.json',
            '/dbt test/graph.gpickle',
        ];

        foreach ($expectedPaths as $path) {
            self::assertFileExists($artifacts->getDownloadDir() . $path);
        }
    }

    public function testDownloadLastRunNotFound(): void
    {
        $temp = new Temp();
        $artifacts = new ArtifactsService($this->storageClient, $temp->getTmpFolder());

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('No artifact from previous run found. Run the component first.');
        $artifacts->downloadLastRun(Component::COMPONENT_ID, '456', 'default');
    }

    public function testUploadResults(): void
    {
        //@todo
        self::assertTrue(true);
    }

    public function testReadFromFile(): void
    {
        $fileId = $this->uploadTestArtifact();
        sleep(1);

        $temp = new Temp();
        $artifacts = new ArtifactsService($this->storageClient, $temp->getTmpFolder());
        $downloadedFileId = $artifacts->downloadLastRun(Component::COMPONENT_ID, '123', 'default');
        self::assertEquals($fileId, $downloadedFileId);

        $manifest = json_decode(
            $artifacts->readFromFile('dbt docs generate', 'manifest.json'),
            true
        );
        self::assertArrayHasKey('metadata', $manifest);

        $runResults = json_decode(
            $artifacts->readFromFile('dbt run', 'run_results.json'),
            true
        );
        self::assertArrayHasKey('results', $runResults);
    }

    public function testGetCompiledSqlFiles(): void
    {
        $dataDir = __DIR__ . '/../data';
        $artifacts = new ArtifactsService($this->storageClient, $dataDir);
        $compiled = $artifacts->getCompiledSqlFiles();

        self::assertArrayHasKey('source_not_null_in.c-test-bucket_test__id_.sql', $compiled);
        self::assertArrayHasKey('source_unique_in.c-test-bucket_test__id_.sql', $compiled);
        self::assertArrayHasKey('fct_model.sql', $compiled);
        self::assertArrayHasKey('stg_model.sql', $compiled);

        self::assertStringContainsString(
            'from "SAPI_9317"."in.c-test-bucket"."test"',
            $compiled['source_not_null_in.c-test-bucket_test__id_.sql']
        );

        self::assertStringContainsString(
            '"id" as unique_field,',
            $compiled['source_unique_in.c-test-bucket_test__id_.sql']
        );
        self::assertStringContainsString(
            'from "SAPI_9317"."in.c-test-bucket"."test"',
            $compiled['source_unique_in.c-test-bucket_test__id_.sql']
        );

        self::assertStringContainsString(
            'from "SAPI_9317"."WORKSPACE_875822722"."stg_model"',
            $compiled['fct_model.sql']
        );

        self::assertStringContainsString(
            'select * from "SAPI_9317"."in.c-test-bucket"."test"',
            $compiled['stg_model.sql']
        );
    }

    public function testGetCompiledSqlFilesNotFound(): void
    {
        $temp = new Temp();
        $artifacts = new ArtifactsService($this->storageClient, $temp->getTmpFolder());

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Compiled SQL files not found in artifact. Run "dbt compile" step first.');

        $artifacts->getCompiledSqlFiles();
    }

    protected function uploadTestArtifact(): int
    {
        $options = new FileUploadOptions();
        $options->setTags([
            'artifact',
            'branchId-default',
            'componentId-' . Component::COMPONENT_ID,
            'configId-123',
            'jobId-123',
        ]);

        return (int) $this->storageClient->uploadFile($this->getArtifactArchivePath(), $options);
    }

    protected function getArtifactArchivePath(): string
    {
        return __DIR__ . '/../data/artifacts.tar.gz';
    }
}
