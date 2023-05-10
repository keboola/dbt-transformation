<?php

declare(strict_types=1);

namespace DbtTransformation\Service;

use DbtTransformation\Helper\DbtCompileHelper;
use DbtTransformation\Helper\DbtDocsHelper;
use Keboola\Component\UserException;
use Keboola\StorageApi\Client as StorageClient;
use Keboola\StorageApi\Options\ListFilesOptions;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Throwable;

class ArtifactsService
{
    private StorageClient $storageClient;
    private Filesystem $filesystem;
    private string $artifactsDir;
    private string $downloadDir;
    /** @var array<string, bool> */
    private array $options;

    /**
     * @param array<string, bool> $options
     */
    public function __construct(
        StorageClient $storageClient,
        string $dataDir,
        array $options = []
    ) {
        $this->storageClient = $storageClient;
        $this->filesystem = new Filesystem();
        $this->artifactsDir = $dataDir . '/artifacts';
        $this->downloadDir = $this->artifactsDir . '/in';
        $this->options = $options;

        $this->filesystem->mkdir([
            $this->artifactsDir,
            $this->downloadDir,
        ]);
    }

    public function getDownloadDir(): string
    {
        return $this->downloadDir;
    }

    public function resolveCommandDir(string $command): ?string
    {
        $commandMap = [
            'build' => 'dbt build',
            'run' => 'dbt run',
            'docs' => 'dbt docs generate',
            'test' => 'dbt test',
            'source' => 'dbt source freshness',
            'seed' => 'dbt seed',
        ];

        foreach ($commandMap as $commandRoot => $dir) {
            if (strpos($command, $commandRoot) !== false) {
                return $dir;
            }
        }

        return null;
    }

    public function writeResults(string $projectPath, string $step): void
    {
        $stepDir = $this->resolveCommandDir($step);
        $isArchive = $this->options['zip'] ?? true;

        if ($stepDir) {
            if ($isArchive) {
                $artifactsPath = sprintf('%s/out/current/%s', $this->artifactsDir, $stepDir);
                $this->filesystem->mkdir($artifactsPath);
                $this->filesystem->mirror(sprintf('%s/target/', $projectPath), $artifactsPath);
                // add logs
                $logsPath = sprintf('%s/logs/', $projectPath);
                if (file_exists($logsPath)) {
                    $this->filesystem->mirror($logsPath, $artifactsPath);
                }
            } else {
                $artifactsPath = sprintf('%s/out/current', $this->artifactsDir);
                $this->filesystem->mkdir($artifactsPath);
                $targetPath = sprintf('%s/target', $projectPath);

                switch ($stepDir) {
                    case 'dbt run':
                        $compiledSqlContent = DbtCompileHelper::getCompiledSqlFilesContent($targetPath);
                        file_put_contents($artifactsPath . '/compiled_sql.json', $compiledSqlContent);

                        $manifestJson = (string) file_get_contents($targetPath . '/manifest.json');
                        $manifest = (array) json_decode($manifestJson, true, 512, JSON_THROW_ON_ERROR);
                        $runResultsJson = (string) file_get_contents($targetPath . '/run_results.json');
                        /** @var array<string, array<string, mixed>> $runResults */
                        $runResults = (array) json_decode($runResultsJson, true, 512, JSON_THROW_ON_ERROR);
                        $modelTimingJson = DbtDocsHelper::getModelTiming($manifest, $runResults);
                        file_put_contents($artifactsPath . '/model_timing.json', json_encode($modelTimingJson));

                        // add logs
                        $logsPath = sprintf('%s/logs/', $projectPath);
                        if (file_exists($logsPath)) {
                            $this->filesystem->mirror($logsPath, $artifactsPath);
                        }

                        $filesToCopy = [
                            'run_results.json',
                            'manifest.json',
                        ];

                        foreach ($filesToCopy as $fileToCopy) {
                            $this->filesystem->copy(
                                sprintf('%s/%s', $targetPath, $fileToCopy),
                                sprintf('%s/%s', $artifactsPath, $fileToCopy)
                            );
                        }

                        break;
                    case 'dbt docs generate':
                        $html = (string) file_get_contents($targetPath . '/index.html');
                        $manifest = (string) file_get_contents($targetPath . '/manifest.json');
                        $catalog = (string) file_get_contents($targetPath . '/catalog.json');
                        $finalHtml = DbtDocsHelper::mergeHtml($html, $catalog, $manifest);
                        file_put_contents($artifactsPath . '/index.html', $finalHtml);

                        break;
                }
            }
        }
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function downloadLastRun(string $componentId, string $configId, string $branchId): ?int
    {
        $query = sprintf(
            'tags:(artifact AND branchId-%s AND componentId-%s AND configId-%s NOT shared)',
            $branchId,
            $componentId,
            $configId
        );

        $files = $this->storageClient->listFiles(
            (new ListFilesOptions())
                ->setQuery($query)
                ->setLimit(1)
        );

        if (empty($files)) {
            throw new UserException(
                'No artifact from previous run found. Run the component first.'
            );
        }

        $file = array_shift($files);
        try {
            $this->filesystem->mkdir($this->artifactsDir . '/tmp');
            $tmpPath = $this->artifactsDir . '/tmp/' . $file['id'];
            $this->storageClient->downloadFile($file['id'], $tmpPath);
            $this->extractArchive($tmpPath, $this->downloadDir);
        } catch (Throwable $e) {
            throw new UserException(sprintf(
                'Error downloading artifact file id "%s": %s',
                $file['id'],
                $e->getMessage()
            ));
        }

        return $file['id'];
    }

    public function downloadByName(string $name, string $componentId, string $configId, string $branchId): ?int
    {
        $query = sprintf(
            'name:%s AND tags:(artifact AND branchId-%s AND componentId-%s AND configId-%s NOT shared)',
            $name,
            $branchId,
            $componentId,
            $configId
        );

        $files = $this->storageClient->listFiles(
            (new ListFilesOptions())
                ->setQuery($query)
                ->setLimit(1)
        );

        if (empty($files)) {
            throw new UserException(
                'No artifact from previous run found. Run the component first.'
            );
        }

        $file = array_shift($files);
        try {
            $this->storageClient->downloadFile(
                $file['id'],
                $this->downloadDir . '/' . $file['name'],
            );
        } catch (Throwable $e) {
            throw new UserException(sprintf(
                'Error downloading artifact file id "%s": %s',
                $file['id'],
                $e->getMessage()
            ));
        }

        return $file['id'];
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function readFromFileInStep(string $step, string $filePath): string
    {
        $file = new SplFileInfo(sprintf('%s/%s/%s', $this->getDownloadDir(), $step, $filePath));
        if (!$file->isFile()) {
            throw new UserException(sprintf('Missing "%s" file in downloaded artifact', $filePath));
        }
        return (string) file_get_contents($file->getPathname());
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function readFromFile(string $filename): string
    {
        $file = new SplFileInfo(sprintf('%s/%s', $this->getDownloadDir(), $filename));
        if (!$file->isFile()) {
            throw new UserException(sprintf('Missing "%s" file in downloaded artifact', $filename));
        }
        return (string) file_get_contents($file->getPathname());
    }

    private function extractArchive(string $sourcePath, string $targetPath): void
    {
        $this->mkdir($targetPath);
        $process = new Process([
            'tar',
            '-xf',
            $sourcePath,
            '-C',
            $targetPath,
        ]);
        $process->mustRun();
    }

    private function mkdir(string $path): void
    {
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->mkdir($path);
        }
    }

    /**
     * @throws \Keboola\Component\UserException
     */
    public function checkIfCorrectStepIsDownloaded(string $step): void
    {
        $docsPath = sprintf('%s/%s', $this->getDownloadDir(), $step);
        if (!$this->filesystem->exists($docsPath)) {
            throw new UserException('No artifact from previous dbt docs generate found. ' .
                'Run the component first with dbt docs generate command enabled.');
        }
    }
}
