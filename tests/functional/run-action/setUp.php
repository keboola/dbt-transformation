<?php

declare(strict_types=1);

use DbtTransformation\FunctionalTests\DatadirTest;
use DbtTransformation\FunctionalTests\OdbcTestConnection;

return static function (DatadirTest $test): void {
    $connection = OdbcTestConnection::createConnection();

    $connection->query('CREATE TABLE "test"(
      "id" VARCHAR, 
      "col2" VARCHAR,  
      "col3" VARCHAR,
      "col4" VARCHAR
    )');

    $connection->query("INSERT INTO \"test\" VALUES 
     ('1', 'a', 'b', 'c'), ('2','d','e','f'), ('3','g','h','i'), ('4','j','k','l');");
};