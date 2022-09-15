<?php

declare(strict_types=1);

namespace DbtTransformation\SyncAction;

class DocsHelper
{
    public static function mergeHtml(string $html, string $catalogJson, string $manifestJson): string
    {
        $searchStr = 'o=[i("manifest","manifest.json"+t),i("catalog","catalog.json"+t)]';
        $newStr = sprintf(
            'o=[{label: \'manifest\', data: "%s"},{label: \'catalog\', data: "%s"}]',
            $manifestJson,
            $catalogJson
        );

        return (string) str_replace($searchStr, $newStr, $html);
    }
}
