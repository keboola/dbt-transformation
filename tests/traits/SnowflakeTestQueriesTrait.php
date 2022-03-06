<?php

declare(strict_types=1);

namespace DbtTransformation\Traits;

use Keboola\SnowflakeDbAdapter\Connection;

trait SnowflakeTestQueriesTrait
{

    private Connection $connection;

    protected function removeAllTablesAndViews(): void
    {
        $dropQueries = $this->connection->fetchAll("
             select 'DROP '  || IFF(table_type = 'VIEW', 'VIEW', 'TABLE') || ' \"' || table_name || '\";' QUERY
             from information_schema.tables
             where  table_schema = current_schema();
         ");

        foreach ($dropQueries as $dropQuery) {
            $this->connection->query($dropQuery['QUERY']);
        }
    }

    protected function createTestTableWithSampleData(): void
    {
        $this->connection->query('CREATE TABLE "test"(
          "id" VARCHAR, 
          "col2" VARCHAR,  
          "col3" VARCHAR,
          "col4" VARCHAR
        )');

        $this->connection->query("INSERT INTO \"test\" VALUES 
            ('1', 'a', 'b', 'c'), ('2','d','e','f'), ('3','g','h','i'), ('4','j','k','l');");
    }
}
