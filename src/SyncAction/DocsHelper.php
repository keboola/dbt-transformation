<?php

declare(strict_types=1);

namespace DbtTransformation\SyncAction;

class DocsHelper
{
    public static function mergeHtml(string $html, string $catalogJson, string $manifestJson): string
    {
        $searchStr = 'o=[i("manifest","manifest.json"+t),i("catalog","catalog.json"+t)]';
        $newStr = sprintf(
            'o=[{label: \'manifest\', data: %s},{label: \'catalog\', data: %s}]',
            $manifestJson,
            $catalogJson
        );

        return (string) str_replace($searchStr, $newStr, $html);
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<string, mixed> $runResults
     * @return array<int, array<string, mixed>>
     */
    public static function getModelTiming(array $manifest, array $runResults): array
    {
        $nodes = $manifest['nodes'];
        $result = [];

        foreach ($runResults['results'] as $run) {
            $node = $nodes[$run['unique_id']];

            $executeTimings = array_filter($run['timing'], function ($item) {
                return $item['name'] === 'execute';
            });
            $executeTiming = array_pop($executeTimings);

            $idArray = explode('.', $run['unique_id']);

            $dependsOn = array_filter($node['depends_on']['nodes'], function ($node) {
                $idArray = explode('.', $node);
                return array_shift($idArray) === 'model';
            });

            $result[] = [
                'id' => $run['unique_id'],
                'name' => array_pop($idArray),
                'status' => $run['status'],
                'timeStarted' => $executeTiming['started_at'],
                'timeCompleted' => $executeTiming['completed_at'],
                'dependsOn' => array_unique($dependsOn),
            ];
        }

        return $result;
    }
}
