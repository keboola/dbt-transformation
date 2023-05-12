<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Service;

use DbtTransformation\Component;
use DbtTransformation\Helper\DbtDocsHelper;
use DbtTransformation\Service\ArtifactsService;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client as StorageClient;
use Keboola\StorageApi\Options\FileUploadOptions;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;

class ArtifactsServiceTest extends TestCase
{
    private StorageClient $storageClient;
    private string $runId;

    public function setUp(): void
    {
        $this->storageClient = new StorageClient([
            'url' => (string) getenv('KBC_URL'),
            'token' => (string) getenv('KBC_TOKEN'),
        ]);

        $this->runId = (string) rand(10000, 99999);
    }

    /** @dataProvider commandsProvider */
    public function testResolveCommandDir(string $fullCommand, ?string $expected): void
    {
        $temp = new Temp();
        $artifacts = new ArtifactsService($this->storageClient, $temp->getTmpFolder());

        self::assertEquals($expected, $artifacts->resolveCommandDir($fullCommand));
    }

    /**
     * @return array<string, array<array-key, ?string>> iterable
     */
    public function commandsProvider(): iterable
    {
        yield 'no params' => [
            'dbt run',
            'dbt run',
        ];

        yield 'with flag' => [
            'dbt run --full-refresh',
            'dbt run',
        ];

        yield 'with flag 2' => [
            'dbt --something run --full-refresh',
            'dbt run',
        ];

        yield 'with argument' => [
            'dbt test --project-dir some/dir',
            'dbt test',
        ];

        yield 'unsupported command' => [
            'dbt rocks --hard',
            null,
        ];

        yield 'unsupported command 2' => [
            'dbt deps',
            null,
        ];
    }

    public function testDownloadLastRun(): void
    {
        $fileId = $this->uploadTestArtifact();
        sleep(1);

        $temp = new Temp();
        $artifacts = new ArtifactsService($this->storageClient, $temp->getTmpFolder());
        $downloadedFileId = $artifacts->downloadLastRun(Component::COMPONENT_ID, $this->runId, 'default');
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

    public function testWriteResultsNoZip(): void
    {
        $temp = new Temp();
        $tmpFolder = $temp->getTmpFolder();
        $artifacts = new ArtifactsService($this->storageClient, $tmpFolder, [
            'zip' => false,
        ]);

        $artifacts->writeResults($this->getProjectPath(), 'dbt run');

        self::assertFileExists($tmpFolder . '/artifacts/out/current/compiled_sql.json');
        self::assertFileExists($tmpFolder . '/artifacts/out/current/manifest.json');
        self::assertFileExists($tmpFolder . '/artifacts/out/current/run_results.json');
        self::assertFileExists($tmpFolder . '/artifacts/out/current/model_timing.json');
        self::assertFileExists($tmpFolder . '/artifacts/out/current/dbt.log');
    }

    public function testReadFromFileInStep(): void
    {
        $fileId = $this->uploadTestArtifact();
        sleep(1);

        $temp = new Temp();
        $artifacts = new ArtifactsService($this->storageClient, $temp->getTmpFolder());
        $downloadedFileId = $artifacts->downloadLastRun(Component::COMPONENT_ID, $this->runId, 'default');
        self::assertEquals($fileId, $downloadedFileId);

        $manifest = (array) json_decode(
            $artifacts->readFromFileInStep('dbt docs generate', 'manifest.json'),
            true
        );
        self::assertArrayHasKey('metadata', $manifest);

        $runResults = (array) json_decode(
            $artifacts->readFromFileInStep('dbt run', 'run_results.json'),
            true
        );
        self::assertArrayHasKey('results', $runResults);
    }

    public function testReadFromFileNoZip(): void
    {
        $temp = new Temp();
        $tmpFolder = $temp->getTmpFolder();

        $this->uploadTestArtifactsNoZip($tmpFolder);

        $artifacts = new ArtifactsService($this->storageClient, $tmpFolder, [
            'zip' => false,
        ]);

        $artifacts->downloadByName(
            'manifest.json',
            Component::COMPONENT_ID,
            '123',
            'default',
        );
        $manifestRaw = $artifacts->readFromFile('manifest.json');
        /** @var array<string, array<string, string>> $manifestJson */
        $manifestJson = json_decode($manifestRaw, true);
        self::assertEquals(
            'https://schemas.getdbt.com/dbt/manifest/v6.json',
            $manifestJson['metadata']['dbt_schema_version']
        );

        $artifacts->downloadByName(
            'run_results.json',
            Component::COMPONENT_ID,
            '123',
            'default',
        );
        $runResultsRaw = $artifacts->readFromFile('run_results.json');
        /** @var array<string, array<string, string>> $runResultsJson */
        $runResultsJson = json_decode($runResultsRaw, true);
        self::assertNotEmpty($runResultsJson['results']);

        $artifacts->downloadByName(
            'model_timing.json',
            Component::COMPONENT_ID,
            '123',
            'default',
        );
        $modelTimingRaw = $artifacts->readFromFile('model_timing.json');
        /** @var array<array-key, array<string, string>> $modelTimingJson */
        $modelTimingJson = json_decode($modelTimingRaw, true);
        self::assertEquals('model.beer_analytics.breweries', $modelTimingJson[0]['id']);
    }

    protected function uploadTestArtifact(): int
    {
        $options = new FileUploadOptions();
        $options->setTags([
            'artifact',
            'branchId-default',
            'componentId-' . Component::COMPONENT_ID,
            sprintf('configId-%s', $this->runId),
            'jobId-123',
        ]);

        return (int) $this->storageClient->uploadFile($this->getArtifactArchivePath(), $options);
    }

    /**
     * @return array<string, int>
     * @throws \JsonException
     * @throws \Keboola\StorageApi\ClientException
     */
    protected function uploadTestArtifactsNoZip(string $tmpFolder): array
    {
        $artifacts = new ArtifactsService($this->storageClient, $tmpFolder, [
            'zip' => false,
        ]);

        $artifacts->writeResults($this->getProjectPath(), 'dbt run');

        $options = new FileUploadOptions();
        $options->setTags([
            'artifact',
            'branchId-default',
            'componentId-' . Component::COMPONENT_ID,
            'configId-123',
            'jobId-123',
        ]);

        self::assertFileExists($tmpFolder . '/artifacts/out/current/manifest.json');
        self::assertFileExists($tmpFolder . '/artifacts/out/current/run_results.json');
        self::assertFileExists($tmpFolder . '/artifacts/out/current/model_timing.json');

        $manifestFileIdId = $this->storageClient->uploadFile(
            $tmpFolder . '/artifacts/out/current/manifest.json',
            $options
        );
        $runResultsFileId = $this->storageClient->uploadFile(
            $tmpFolder . '/artifacts/out/current/run_results.json',
            $options
        );
        $modelTimingFileId = $this->storageClient->uploadFile(
            $tmpFolder . '/artifacts/out/current/model_timing.json',
            $options
        );

        return [
            'manifest' => $manifestFileIdId,
            'run_results' => $runResultsFileId,
            'model_timing' => $modelTimingFileId,
        ];
    }

    protected function getArtifactArchivePath(): string
    {
        return __DIR__ . '/../data/artifacts.tar.gz';
    }

    protected function getProjectPath(): string
    {
        return __DIR__ . '/../data';
    }
}
