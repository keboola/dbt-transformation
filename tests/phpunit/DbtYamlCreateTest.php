<?php

declare(strict_types=1);

namespace DbtTransformation\Tests;

use DbtTransformation\DbtYamlCreateService\DbtProfilesYamlCreateService;
use DbtTransformation\DbtYamlCreateService\DbtSourceYamlCreateService;
use Generator;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DbtYamlCreateTest extends TestCase
{
    protected string $dataDir = __DIR__ . '/../data';
    protected string $providerDataDir = __DIR__ . '/data';

    /**
     * @dataProvider profileYamlDataProvider
     * @param array<string, mixed> $config
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlFromConfigData(
        array $config,
        string $generatedFilePath,
        string $expectedSourceFilePath
    ): void {
        $service = new DbtProfilesYamlCreateService();
        $service->dumpYaml(
            $this->dataDir,
            sprintf('%s/dbt_project.yml', $this->providerDataDir),
            $config['authorization']['workspace']
        );
        self::assertFileEquals($expectedSourceFilePath, $generatedFilePath);
    }

    /**
     * @dataProvider profileYamlDataProvider
     * @param array<string, mixed> $config
     * @throws \Keboola\Component\UserException
     */
    public function testCreateProfileYamlMissingDbtProjectFile(array $config): void
    {
        $this->expectException(UserException::class);
        $this->expectErrorMessage('Missing file "dbt_project.yml" in your project root');

        $service = new DbtProfilesYamlCreateService();
        $service->dumpYaml(
            $this->dataDir,
            sprintf('%s/non-exist.yml', $this->providerDataDir),
            $config['authorization']['workspace']
        );
    }

    /**
     * @dataProvider sourceYamlDataProvider
     * @param array<string, mixed> $config
     */
    public function testCreateSourceYamlFromConfigData(
        array $config,
        string $generatedFilePath,
        string $expectedSourceFilePath
    ): void {
        $service = new DbtSourceYamlCreateService();
        $service->dumpYaml(
            $this->dataDir,
            $config['parameters']['dbt']['sourceName'],
            $config['authorization']['workspace'],
            $config['storage']['input']['tables']
        );

        self::assertFileEquals($expectedSourceFilePath, $generatedFilePath);
    }

    /**
     * @return Generator<int, mixed>
     * @throws \JsonException
     */
    public function profileYamlDataProvider(): Generator
    {
        yield [
            'config' => $this->getConfig(),
            'generatedFilePath' => sprintf('%s/.dbt/profiles.yml', $this->dataDir),
            'expectedSourceFilePath' => sprintf('%s/expectedProfiles.yml', $this->providerDataDir),
        ];
    }

    /**
     * @return Generator<int, mixed>
     * @throws \JsonException
     */
    public function sourceYamlDataProvider(): Generator
    {
        $config = $this->getConfig();

        yield [
            'config' => $config,
            'generatedFilePath' => sprintf(
                '%s/models/src_%s.yml',
                $this->dataDir,
                $config['parameters']['dbt']['sourceName']
            ),
            'expectedSourceFilePath' => sprintf('%s/expectedSource.yml', $this->providerDataDir),
        ];
    }

    /**
     * @return array<string, mixed>
     * @throws \JsonException
     */
    protected function getConfig(): array
    {
        $configJson = file_get_contents(sprintf('%s/config.json', $this->providerDataDir));
        if ($configJson === false) {
            throw new RuntimeException('Failed to get contents of config.json');
        }

        return json_decode(
            $configJson,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
