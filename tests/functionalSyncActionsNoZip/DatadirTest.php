<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalSyncActionsTestsNoZip;

use DbtTransformation\Component;
use DbtTransformation\Service\ArtifactsService;
use Keboola\DatadirTests\DatadirTestCase;
use Keboola\DatadirTests\DatadirTestSpecificationInterface;
use Keboola\DatadirTests\Exception\DatadirTestsException;
use Keboola\StorageApi\Client as StorageClient;
use Keboola\StorageApi\Options\FileUploadOptions;
use Keboola\Temp\Temp;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Throwable;

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

        $files = ['manifest.json', 'run_results.json', 'model_timing.json', 'compiled_sql.json'];

        foreach ($files as $file) {
            $filePath = sprintf('%s/artifacts/out/current/%s', $tmpFolder, $file);
            self::assertFileExists($filePath);
            $storageClient->uploadFile($filePath, $options);
        }

        sleep(1);
    }

    protected function runScript(string $datadirPath, ?string $runId = null): Process
    {
        $fs = new Filesystem();

        $script = $this->getScript();
        if (!$fs->exists($script)) {
            throw new DatadirTestsException(sprintf(
                'Cannot open script file "%s"',
                $script,
            ));
        }

        $runCommand = [
            'php',
            $script,
        ];
        $runProcess = new Process($runCommand);
        $defaultRunId = random_int(1000, 100000) . '.' . random_int(1000, 100000) . '.' . random_int(1000, 100000);
        $runProcess->setEnv([
            'KBC_DATADIR' => $datadirPath,
            'KBC_RUNID' => $runId ?? $defaultRunId,
            'KBC_BRANCHID' => getenv('KBC_BRANCHID') ?: '',
        ]);
        $runProcess->setTimeout(0.0);
        $runProcess->run();
        return $runProcess;
    }

    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in(__DIR__ . '/../../data'));
    }

    protected function assertMatchesSpecification(
        DatadirTestSpecificationInterface $specification,
        Process $runProcess,
        string $tempDatadir,
    ): void {
        if ($specification->getExpectedReturnCode() !== null) {
            $this->assertProcessReturnCode($specification->getExpectedReturnCode(), $runProcess);
        } else {
            $this->assertNotSame(0, $runProcess->getExitCode(), 'Exit code should have been non-zero');
        }
        if ($specification->getExpectedStdout() !== null) {
            try {
                $this->assertStringMatchesFormat(
                    trim($specification->getExpectedStdout()),
                    trim($runProcess->getOutput()),
                    'Failed asserting stdout output',
                );
            } catch (Throwable $e) {
                //dbt-compile output json is too large to be compared with assertStringMatchesFormat
                if (str_contains($e->getMessage(), 'regular expression is too large')) {
                    self::assertEquals(
                        trim($specification->getExpectedStdout()),
                        trim($runProcess->getOutput()),
                        'Failed asserting stdout output',
                    );
                } else {
                    throw $e;
                }
            }
        }
        if ($specification->getExpectedStderr() !== null) {
            $this->assertStringMatchesFormat(
                trim($specification->getExpectedStderr()),
                trim($runProcess->getErrorOutput()),
                'Failed asserting stderr output',
            );
        }
        if ($specification->getExpectedOutDirectory() !== null) {
            $this->assertDirectoryContentsSame(
                $specification->getExpectedOutDirectory(),
                $tempDatadir . '/out',
            );
        }
    }
}
