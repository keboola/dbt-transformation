<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper\OutputManifest;

interface OutputManifestInterface
{
    /**
     * @param array<int, array<string, string>> $configuredOutputTables
     */
    public function dump(array $configuredOutputTables): void;
}
