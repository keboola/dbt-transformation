select
    "id" as unique_field,
    count(*) as n_records
from "SAPI_9317"."in.c-test-bucket"."test"
where "id" is not null
group by "id"
having count(*) > 1
