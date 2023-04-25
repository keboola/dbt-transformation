

SELECT
  "id"                    AS brewery_id,
  trim("name")            AS brewery_name,
  trim("city")            AS brewery_city,
  trim("state")           AS brewery_state,
  'USA'                 AS brewery_country
FROM
  "KEBOOLA_9457"."in.c-dbt_beer_seed"."seed_breweries"