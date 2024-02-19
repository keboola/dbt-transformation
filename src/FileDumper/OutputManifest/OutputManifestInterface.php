<?php

declare(strict_types=1);

namespace DbtTransformation\FileDumper\OutputManifest;

interface OutputManifestInterface
{
    public function dump(): void;
}
