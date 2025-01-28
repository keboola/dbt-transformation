<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

interface DwhProviderInterface
{
    public function createDbtYamlFiles(string $profilesPath): void;

    public function getDwhConnectionType(): DwhConnectionTypeEnum;
}
