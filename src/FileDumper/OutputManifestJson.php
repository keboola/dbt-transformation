<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper;

class OutputManifestJson extends FilesystemAwareDumper
{
    private string $outputDir;

    public function __construct(string $dataDir)
    {
        parent::__construct();
        $this->outputDir = $dataDir . '/out/tables';
    }

    /**
     * @param array<string, array<int|string, mixed>> $manifestData
     */
    public function dumpJson(string $tableName, array $manifestData): void
    {
        $this->filesystem->dumpFile(
            sprintf('%s/%s.manifest', $this->outputDir, $tableName),
            (string) json_encode(array_filter($manifestData))
        );
    }
}
