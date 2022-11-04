
# dbt transformation

## Configuration

The configuration `config.json` contains following properties in `parameters` key:
- `git` - object (required): Configuration of repository with DBT project.
    - `repo` - string (required): URL of GitHub repository with your DBT project.
    - `username` - string (optional): GitHub username if repository is private.
    - `#password` - string (optional): GitHub Private Access Token if repository is private. Both or none of couple `username` and `password` must be specified.
    - `branch` - string (optional): Specify git branch if you want to clone project from specific branch.
- `dbt` - object (required): Configuration of DBT
    - `executeSteps` - array of strings (required): Enum values of dbt steps you want to run. Available values are `dbt build`, `dbt run`, `dbt docs generate`, `dbt test`, `dbt source freshness`, `dbt debug`, `dbt compile` and `dbt seed`. At least one value required.
    - `modelNames` - array of strings (optional): If you want to run DBT only with certain models, you can specify their names here. Otherwise, all models will be run.
    - `threads` - integer 1 - 8, default 4 (optional): Maximum number of paths through the graph dbt may work on at once.
    - `freshness` - object (required): Configuration of freshness.
      - `warn_after` - object (optional)
        - `period` - positive integer: Number of periods where a data source is still considered "fresh".
        - `count` - string enum: The time period used in the freshness calculation. One of `minute`, `hour` or `day`.
      - `error_after` - object (optional)
        - `period` - positive integer: Number of periods where a data source is still considered "fresh".
        - `count` - string enum: The time period used in the freshness calculation. One of `minute`, `hour` or `day`.
- `showExecutedSqls` - boolean (optional): Default `false`, if set to `true` SQL queries executed by DBT transformation are printed to output.
- `generateSources` - boolean (optional): Default `true`
  - If `true` sources YAML files are generated. 
  - If `false` generating of sources file is skipped. 

Example:
```json
{
  "git": {
    "repo": "https://github.com/padak/dbt-demo.git"
  },
  "dbt": {
    "executeSteps": [
      "dbt run"
    ],
    "threads": 4,
    "modelNames": [
      "+final_visit_hour"
    ],
    "freshness": {
      "warn_after": {"count": 1, "period": "minute"},
      "error_after": {"count": 1, "period": "day"}
    }
  },
  "showExecutedSqls": true,
  "generateSources": true
}
```

## Development
 
Clone this repository and init the workspace with following command:

```shell
git clone https://github.com/keboola/dbt-transformation.git
cd dbt-transformation
docker-compose build #on M1 add flag: --build-arg TARGETPLATFORM=linux/arm64
docker-compose run --rm app composer install --no-scripts
```

Run the test suite using this command:

```shell
docker-compose run --rm dev composer ci
```
 
# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/) 

## License

MIT licensed, see [LICENSE](./LICENSE) file.
