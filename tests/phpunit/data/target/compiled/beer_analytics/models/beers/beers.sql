

SELECT
  "id"            AS beer_id,
  TRIM("name")    AS beer_name,
  "style"         AS beer_style,
  TRY_TO_NUMERIC("abv",14,8)           AS abv,
  TRY_TO_NUMERIC("ibu",14,8)           AS ibu,
  CASE 
       WHEN ibu <= 50 THEN 'Malty'
       WHEN ibu > 50 THEN 'Hoppy'
   END AS bitterness,
  "brewery_id"    AS brewery_id,
  "ounces"        AS ounces
FROM
  "KEBOOLA_9457"."in.c-dbt_beer_seed"."seed_beers"