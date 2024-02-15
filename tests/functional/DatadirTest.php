<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalTests;

use Keboola\DatadirTests\DatadirTestCase;
use Keboola\DatadirTests\Exception\DatadirTestsException;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class DatadirTest extends DatadirTestCase
{
    protected function runScript(string $datadirPath, ?string $runId = null): Process
    {
        $fs = new Filesystem();

        $script = $this->getScript();
        if (!$fs->exists($script)) {
            throw new DatadirTestsException(sprintf(
                'Cannot open script file "%s"',
                $script,
            ));
        }

        $runCommand = [
            'php',
            $script,
        ];
        $runProcess = new Process($runCommand);
        $defaultRunId = random_int(1000, 100000) . '.' . random_int(1000, 100000) . '.' . random_int(1000, 100000);
        $environments = [
            'KBC_DATADIR' => $datadirPath,
            'KBC_RUNID' => $runId ?? $defaultRunId,
            'KBC_URL' => $this->getEnv('KBC_URL'),
            'KBC_TOKEN' => $this->getEnv('KBC_TOKEN'),
            'KBC_COMPONENTID' => $this->getEnv('KBC_COMPONENTID'),
        ];
        if (getEnv('KBC_COMPONENT_RUN_MODE')) {
            $environments['KBC_COMPONENT_RUN_MODE'] = $this->getEnv('KBC_COMPONENT_RUN_MODE');
        }

        $runProcess->setEnv($environments);
        $runProcess->setTimeout(0.0);
        $runProcess->run();
        return $runProcess;
    }

    public function tearDown(): void
    {
        $testProjectDir = $this->getTestFileDir() . '/' . $this->dataName();
        $tearDownPhpFile = $testProjectDir . '/tearDown.php';
        if (file_exists($tearDownPhpFile)) {
            $initCallback = require $tearDownPhpFile;
            if (!is_callable($initCallback)) {
                throw new RuntimeException(sprintf('File "%s" must return callback!', $tearDownPhpFile));
            }

            $initCallback($this);
        }

        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in(__DIR__ . '/../../data'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $testProjectDir = $this->getTestFileDir() . '/' . $this->dataName();

        $setUpPhpFile = $testProjectDir . '/setUp.php';
        if (file_exists($setUpPhpFile)) {
            $initCallback = require $setUpPhpFile;
            if (!is_callable($initCallback)) {
                throw new RuntimeException(sprintf('File "%s" must return callback!', $setUpPhpFile));
            }

            $initCallback($this);
        }
    }
}
