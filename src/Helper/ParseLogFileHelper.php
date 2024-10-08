<?php

declare(strict_types=1);

namespace DbtTransformation\Helper;

use Generator;
use SplFileObject;
use function mb_strlen;
use function mb_substr;

class ParseLogFileHelper
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
            $fgets = $file->fgets();
            $logs[] = json_decode($fgets, true);
        }

        /** @var array{
         *     'data': array<string, string>
         * } $log
         */
        foreach ($logs as $log) {
            if (!empty($log['data']['sql'])) {
                yield $this->queryExcerpt(
                    trim((string) preg_replace('!/\*.*?\*/!s', '', $log['data']['sql'])),
                );
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
