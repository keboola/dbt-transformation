<?php

declare(strict_types=1);

namespace DbtTransformation\JobRunner;

use InvalidArgumentException;
use Keboola\StorageApi\Client;
use Keboola\StorageApi\Options\IndexOptions;
use Psr\Log\LoggerInterface;

abstract class JobRunner
{
    protected Client $storageApiClient;

    protected LoggerInterface $logger;

    /** @var null|array<string, mixed> */
    private ?array $services = null;

    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->storageApiClient = $client;
        $this->logger = $logger;
    }

    /**
     * @param array<string, array<string, mixed>> $data
     * @return array<string, mixed>
     */
    abstract public function runJob(string $componentId, array $data): array;

    public function getServiceUrl(string $serviceId): string
    {
        $foundServices = array_values(array_filter($this->getServices(), static function ($service) use ($serviceId) {
            return $service['id'] === $serviceId;
        }));
        if (empty($foundServices)) {
            throw new InvalidArgumentException(sprintf('%s service not found', $serviceId));
        }
        return $foundServices[0]['url'];
    }

    /**
     * @return array<string, mixed>
     */
    private function getServices(): array
    {
        $options = new IndexOptions();
        $options->setExclude(['components']);

        if (!$this->services) {
            $this->services = $this->storageApiClient->indexAction()['services'];
        }
        return $this->services;
    }
}
