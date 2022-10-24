<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class FilesystemAwareDumper
{
    public Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    protected function createFolderIfNotExist(string $folderPath): void
    {
        try {
            if (!$this->filesystem->exists($folderPath)) {
                $this->filesystem->mkdir($folderPath);
            }
        } catch (IOExceptionInterface $e) {
            throw new RuntimeException(sprintf('An error occurred while creating directory "%s"', $folderPath));
        }
    }
}
