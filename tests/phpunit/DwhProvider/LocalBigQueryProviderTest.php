<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\DwhProvider;

use ColinODell\PsrTestLogger\TestLogger;
use DbtTransformation\Config;
use DbtTransformation\Configuration\ConfigDefinition;
use DbtTransformation\FileDumper\BigQueryDbtSourcesYaml;
use DbtTransformation\FileDumper\DbtProfilesYaml;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\Core\Exception\ServiceException;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LocalBigQueryProviderTest extends TestCase
{
    private TestLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new TestLogger();
    }

    private function createConfig(): Config
    {
        return new Config([
            'authorization' => [
                'workspace' => [
                    'schema' => 'WORKSPACE_12345',
                    'region' => 'europe-west3',
                    'credentials' => [
                        'type' => 'service_account',
                        'project_id' => 'test-project',
                        'private_key_id' => 'key-id',
                        'private_key' => 'key',
                        'client_email' => 'test@test.iam.gserviceaccount.com',
                        'client_id' => '123',
                        'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                        'token_uri' => 'https://oauth2.googleapis.com/token',
                        'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                        'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/test',
                    ],
                ],
            ],
            'parameters' => [
                'git' => [
                    'repo' => 'https://github.com/keboola/dbt-test-project-public.git',
                ],
                'dbt' => [
                    'executeSteps' => [
                        ['step' => 'dbt run', 'active' => true],
                    ],
                ],
            ],
        ], new ConfigDefinition());
    }

    private function createProvider(Dataset $dataset): TestableLocalBigQueryProvider
    {
        $config = $this->createConfig();

        $createSourceFileService = $this->createMock(BigQueryDbtSourcesYaml::class);
        $createProfilesFileService = $this->createMock(DbtProfilesYaml::class);

        return new TestableLocalBigQueryProvider(
            $createSourceFileService,
            $createProfilesFileService,
            $this->logger,
            $config,
            '/tmp/test-project',
            $dataset,
        );
    }

    public function testDatasetAccessibleOnFirstAttempt(): void
    {
        $dataset = $this->createMock(Dataset::class);
        $dataset->expects(self::once())
            ->method('update')
            ->with([]);

        $provider = $this->createProvider($dataset);
        $provider->callWaitForDatasetAccessibility();

        self::assertTrue($this->logger->hasInfoThatContains(
            'Workspace dataset "WORKSPACE_12345" is accessible (attempt 1/10).',
        ));
    }

    public function testDatasetAccessibleAfterRetries(): void
    {
        $dataset = $this->createMock(Dataset::class);
        $dataset->expects(self::exactly(3))
            ->method('update')
            ->with([])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new ServiceException('Access denied', 403)),
                self::throwException(new ServiceException('Access denied', 403)),
                null,
            );

        $provider = $this->createProvider($dataset);
        $provider->callWaitForDatasetAccessibility();

        self::assertTrue($this->logger->hasInfoThatContains(
            'Workspace dataset "WORKSPACE_12345" is accessible (attempt 3/10).',
        ));
        // RetryProxy logs retries automatically
        self::assertTrue($this->logger->hasInfoThatContains('Access denied. Retrying... ['));
    }

    public function testDatasetNotAccessibleAfterAllAttempts(): void
    {
        $dataset = $this->createMock(Dataset::class);
        $dataset->expects(self::exactly(10))
            ->method('update')
            ->with([])
            ->willThrowException(new ServiceException('Permission denied', 403));

        $provider = $this->createProvider($dataset);

        $this->expectException(UserException::class);
        $this->expectExceptionMessage(
            'Workspace dataset "WORKSPACE_12345" is not accessible after 10 attempts: Permission denied',
        );

        $provider->callWaitForDatasetAccessibility();
    }

    public function testNonServiceExceptionIsNotRetried(): void
    {
        $dataset = $this->createMock(Dataset::class);
        $dataset->expects(self::once())
            ->method('update')
            ->with([])
            ->willThrowException(new RuntimeException('Network error'));

        $provider = $this->createProvider($dataset);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Network error');

        $provider->callWaitForDatasetAccessibility();
    }
}
