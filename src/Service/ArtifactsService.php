<?php

declare(strict_types=1);

namespace DbtTransformation\Service;

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

    public function __construct(
        StorageClient $storageClient,
        string $dataDir
    ) {
        $this->storageClient = $storageClient;
        $this->filesystem = new Filesystem();
        $this->artifactsDir = $dataDir . '/artifacts';
        $this->downloadDir = $this->artifactsDir . '/in';

        $this->filesystem->mkdir([
            $this->artifactsDir,
            $this->downloadDir,
        ]);
    }

    public function getDownloadDir(): string
    {
        return $this->downloadDir;
    }

    public function uploadResults(string $projectPath, string $step): void
    {
        $artifactsPath = sprintf('%s/out/current/%s', $this->artifactsDir, $step);
        $this->filesystem->mkdir($artifactsPath);
        $this->filesystem->mirror(sprintf('%s/target/', $projectPath), $artifactsPath);
    }

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

    public function readFromFile(string $step, string $filePath): string
    {
        $file = new SplFileInfo(sprintf('%s/%s/%s', $this->getDownloadDir(), $step, $filePath));
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
}
