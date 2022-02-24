<?php

declare(strict_types=1);

namespace DbtTransformation;

use Generator;
use SplFileObject;

class ParseLogFileService
{

    private string $logFilePath;

    public function __construct(string $logFilePath)
    {
        $this->logFilePath = $logFilePath;
    }

    /**
     * @return \Generator<string>
     */
    public function getSqls(): Generator
    {
        $file = new SplFileObject($this->logFilePath);
        $logs = [];
        while (!$file->eof()) {
            $logs[] = $file->fgets() !== false ? json_decode($file->fgets(), true) : null;
        }

        foreach ($logs as $log) {
            if ($log && array_key_exists('sql', $log['data'])) {
                yield $this->queryExcerpt(trim(preg_replace('!/\*.*?\*/!s', '', $log['data']['sql'])));
            }
        }
    }

    private function queryExcerpt(string $query): string
    {
        if (mb_strlen($query) > 1000) {
            return mb_substr($query, 0, 500, 'UTF-8') . "\n...\n" . mb_substr($query, -500, null, 'UTF-8');
        }
        return $query;
    }
}
