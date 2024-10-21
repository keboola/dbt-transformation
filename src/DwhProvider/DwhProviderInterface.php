<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

interface DwhProviderInterface
{
    public function createDbtYamlFiles(): void;

    public function getDwhLocation(): DwhConnectionTypeEnum;
}
