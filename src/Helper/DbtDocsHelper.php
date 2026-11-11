<?php

declare(strict_types=1);

namespace DbtTransformation\Helper;

class DbtDocsHelper
{
    public static function mergeHtml(string $html, string $catalogJson, string $manifestJson): string
    {
        $searchStr = 'o=[i("manifest","manifest.json"+t),i("catalog","catalog.json"+t)]';
        $newStr = sprintf(
            'o=[{label: \'manifest\', data: %s},{label: \'catalog\', data: %s}]',
            $manifestJson,
            $catalogJson,
        );

        $resultHtml = str_replace($searchStr, $newStr, $html, $count);

        if ($count === 0) { // Fallback for new structure
            $fallbackSearchStr = 'n=[o("manifest","manifest.json"+t),o("catalog","catalog.json"+t)]';
            $fallbackNewStr = sprintf(
                'n=[{label: \'manifest\', data: %s},{label: \'catalog\', data: %s}]',
                $manifestJson,
                $catalogJson,
            );

            $resultHtml = str_replace($fallbackSearchStr, $fallbackNewStr, $html);
        }

        return $resultHtml;
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<string, array<string, mixed>> $runResults
     * @return array<int, array<string, mixed>>
     */
    public static function getModelTiming(array $manifest, array $runResults): array
    {
        /** @var array<string, array<string, array<string, array<string, mixed>>>> $nodes */
        $nodes = $manifest['nodes'];
        /** @var array<string, array<string, array<string, array<string, mixed>>>> $unitTests */
        $unitTests = $manifest['unit_tests'] ?? [];
        $result = [];

        /** @var array{
         *     'unique_id': string,
         *     'timing': array<string, array<string, string>>,
         *     'status': string,
         *     'thread_id': string,
         * } $run
         */
        foreach ($runResults['results'] as $run) {
            $node = $nodes[$run['unique_id']] ?? $unitTests[$run['unique_id']] ?? null;
            if ($node === null) {
                continue;
            }
            /** @var array{
             *     'depends_on': array{'nodes'?: array<string>, 'macros'?: array<string>}
             * } $node
             */

            $executeTimings = array_filter($run['timing'], function ($item) {
                return $item['name'] === 'execute';
            });
            /** @var array{
             *     'started_at': string,
             *     'completed_at': string,
             * } $executeTiming
             */
            $executeTiming = array_pop($executeTimings);

            $idArray = explode('.', $run['unique_id']);

            $dependsOn = array_filter($node['depends_on']['nodes'] ?? [], function ($node) {
                $idArray = explode('.', $node);
                return array_shift($idArray) === 'model';
            });

            $result[] = [
                'id' => $run['unique_id'],
                'name' => array_pop($idArray),
                'status' => $run['status'],
                'thread' => $run['thread_id'],
                'timeStarted' => $executeTiming['started_at'],
                'timeCompleted' => $executeTiming['completed_at'],
                'dependsOn' => array_unique($dependsOn),
            ];
        }

        return $result;
    }
}
