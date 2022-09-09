<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalTests;

use Keboola\DatadirTests\DatadirTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DatadirTest extends DatadirTestCase
{
    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in(__DIR__ . '/../../data'));
    }
}
