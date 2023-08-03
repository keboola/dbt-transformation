<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalSyncActionsTests;

use DbtTransformation\Component;
use Keboola\DatadirTests\DatadirTestCase;
use Keboola\DatadirTests\Exception\DatadirTestsException;
use Keboola\StorageApi\Client as StorageClient;
use Keboola\StorageApi\Options\FileUploadOptions;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

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

    protected function runScript(string $datadirPath, ?string $runId = null): Process
    {
        $fs = new Filesystem();

        $script = $this->getScript();
        if (!$fs->exists($script)) {
            throw new DatadirTestsException(sprintf(
                'Cannot open script file "%s"',
                $script
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
}
