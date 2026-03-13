<?php

declare(strict_types=1);

namespace DbtTransformation\Service;

use Psr\Log\LoggerInterface;

class DbtLogService
{
    private int $offset = 0;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $logFilePath,
    ) {
    }

    public function log(): void
    {
        clearstatcache(true, $this->logFilePath);

        if (!file_exists($this->logFilePath)) {
            return;
        }

        $fileSize = filesize($this->logFilePath);
        if ($fileSize === false) {
            return;
        }

        if ($fileSize < $this->offset) {
            $this->offset = 0;
        }

        $handle = fopen($this->logFilePath, 'r');
        if ($handle === false) {
            return;
        }

        fseek($handle, $this->offset);

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line !== '') {
                $this->logger->info($line);
            }
        }

        $this->offset = (int) ftell($handle);
        fclose($handle);
    }
}
