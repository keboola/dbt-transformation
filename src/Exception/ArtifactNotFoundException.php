<?php

declare(strict_types=1);

namespace DbtTransformation\Exception;

use Exception;

class ArtifactNotFoundException extends Exception
{
    public const TYPE_NO_ZIP = 'no-zip';
    public const TYPE_ZIP = 'zip';

    public function __construct(string $configId, string $type)
    {
        parent::__construct(sprintf('%s artifact for configuration "%s" not found.', ucfirst($type), $configId));
    }
}
