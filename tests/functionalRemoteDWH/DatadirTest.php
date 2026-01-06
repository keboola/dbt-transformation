<?php

declare(strict_types=1);

namespace DbtTransformation\FunctionalRemoteDwhTests;

use Keboola\DatadirTests\DatadirTestCase;
use Keboola\DatadirTests\DatadirTestSpecificationInterface;
use Keboola\DatadirTests\Exception\DatadirTestsException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class DatadirTest extends DatadirTestCase
{
    public function tearDown(): void
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $fs->remove($finder->in(__DIR__ . '/../../data'));
    }

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
            'KBC_COMPONENTID' => 'keboola.dbt-transformation-remote',
            'KBC_DATA_TYPE_SUPPORT' => $this->getEnv('KBC_DATA_TYPE_SUPPORT'),
        ];
        if (getEnv('KBC_COMPONENT_RUN_MODE')) {
            $environments['KBC_COMPONENT_RUN_MODE'] = getEnv('KBC_COMPONENT_RUN_MODE');
        }

        $runProcess->setEnv($environments);
        $runProcess->setTimeout(0.0);
        $runProcess->run();
        return $runProcess;
    }

    /**
     * @dataProvider provideDatadirSpecifications
     */
    public function testDatadir(DatadirTestSpecificationInterface $specification): void
    {
        parent::testDatadir($specification);
    }
}
