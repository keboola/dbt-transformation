
# dbt transformation

## Configuration

The configuration `config.json` contains following properties in `parameters` key:
- `git` - object (required): Configuration of repository with DBT project.
    - `repo` - string (required): URL of GitHub repository with your DBT project.
    - `username` - string (optional): GitHub username if repository is private.
    - `#password` - string (optional): GitHub Private Access Token if repository is private. Both or none of couple `username` and `password` must be specified.
    - `branch` - string (optional): Specify git branch if you want to clone project from specific branch.
    - `folder` - string (optional): Specify folder in repository where dbt project is located. Default is root of repository.
- `dbt` - object (required): Configuration of DBT
    - `executeSteps` - array of array prototypes (required) - at least one element required.
      - `step` - string (required) dbt step you want to run, you can also add some flags e.g. `dbt --warn-error run --select my_model` (but you cannot override some parameters such as `--profile-dir` or `--target` which are used by component itself)
      - `active` - boolean (required) - if step should be executed or not (UI use this for saving order of inactive steps)
    - `modelNames` - **DEPRECATED: use `--select` parameter in execute step instead** *array of strings (optional): If you want to run DBT only with certain models, you can specify their names here. Otherwise, all models will be run.*
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
      {"step": "dbt run --select +final_visit_hour", "active": true}
    ],
    "threads": 4,
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

### Linux
```shell
git clone https://github.com/keboola/dbt-transformation.git
cd dbt-transformation
docker compose build 
docker compose run --rm app composer install --no-scripts
```

### ARM
```
git clone https://github.com/keboola/dbt-transformation.git
cd dbt-transformation
export DOCKER_DEFAULT_PLATFORM=linux/amd64
docker compose build --build-arg TARGETPLATFORM=linux/arm64
docker compose run --rm app composer install --no-scripts
```

Run the test suite using this command:

```shell
docker compose run --rm dev composer ci
```
 
# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/) 

## License

MIT licensed, see [LICENSE](./LICENSE) file.
