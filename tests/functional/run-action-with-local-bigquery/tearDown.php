<?php

declare(strict_types=1);

use DbtTransformation\FunctionalTests\DatadirTest;

return static function (DatadirTest $test): void {
    putenv(sprintf('KBC_URL=%s', getenv('SNFK_KBC_URL')));
    putenv(sprintf('KBC_TOKEN=%s', getenv('SNFK_KBC_TOKEN')));
    putenv(sprintf('KBC_COMPONENTID=%s', 'keboola.dbt-transformation'));
};
