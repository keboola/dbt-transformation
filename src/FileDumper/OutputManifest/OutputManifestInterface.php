<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper\OutputManifest;

interface OutputManifestInterface
{
    /**
     * @param null[]|array<int, array{
     *       destination: string,
     *       source: string,
     *       primary_key?: array<string>,
     *   }> $configuredOutputTables
     */
    public function dump(array $configuredOutputTables): void;
}
