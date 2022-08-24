
# dbt transformation

## Configuration

The configuration `config.json` contains following properties in `parameters` key:
- `git` - object (required): Configuration of repository with DBT project.
    - `repo` - string (required): URL of GitHub repository with your DBT project.
    - `username` - string (optional): GitHub username if repository is private.
    - `password` - string (optional): GitHub Private Access Token if repository is private. Both or none of couple `username` and `password` must be specified.
    - `branch` - string (optional): Specify git branch if you want to clone project from specific branch.
- `dbt` - object (required): Configuration of DBT
    - `executeSteps` - array of strings (required): Enum values of dbt steps you want to run. Available values are `dbt run`, `dbt docs`, `dbt test` and `dbt source freshness`. At least one value required.
    - `modelNames` - array of strings (optional): If you want to run DBT only with certain models, you can specify their names here. Otherwise, all models will be run.
- `showExecutedSqls` - boolean (optional): Default `false`, if set to `true` SQL queries executed by DBT transformation are printed to output.

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
    "modelNames": [
      "+final_visit_hour"
    ]
  },
  "showExecutedSqls": true
}
```

## CLI usage


You can use this component to run your DBT project locally using Keboola Snowflake Workspace as backend. Before using following CLI commands, clone this repository and init the workspace with following command. If you encounter bad CPU type in executable error message, please **install rosetta on M1 Mac** via `softwareupdate --install-rosetta --agree-to-license` 

```shell
git clone https://github.com/keboola/dbt-transformation.git
cd dbt-transformation
docker-compose run --rm app composer install --prefer-dist --no-interaction --no-dev
```

For first time you have to run `docker-compose build app` before. 

If you don't want to enter Connection URL and storage API token every time, you can create file `.env` with these credentials like this:
```dotenv
SAPI_URL=https://connection.keboola.com
SAPI_TOKEN=xxxx
```

### app:clone-repository
```shell
docker-compose run --rm app bin/console app:clone-repository
```
Clones GIT repository with your DBT project. You input path to your repository when you are asked, branch what you want to use (leave blank if you want clone from master branch) and optionally GitHub credentials if repository is private. If you are using another GIT service than GitHub or you just want to clone your project manually, just skip this command and clone your project to folder `/data` and rename it `dbt-project`, so your project root will be in path `/data/dbt-project/` or mount it to docker (see instructions in *app:generate-profiles-and-sources*)

### app:create-workspace
```shell
docker-compose run --rm app bin/console app:create-workspace
```
Creates workspace Snowflake workspace for running your DBT transformations. You need to enter your Keboola Connection URL (e.g. https://connection.keboola.com), storage API token and name for that workspace.

### app:generate-profiles-and-sources
```shell
docker-compose run --rm app bin/console app:generate-profiles-and-sources
```
Generated profiles.yml and source file for every bucket in your project with all tables. You need to input URL and token like in previous command. You need to also provide name of workspace, you created in previous step. All credentials are filled using environment variables, so command also print them for you. You have to export them to environment where your dbt will run. If executed with `--env` flag only environment variables for DBT will be printed, without generating yaml files.
#### Only print envirovment variables
```shell
docker-compose run --rm app bin/console app:generate-profiles-and-sources --env
```
#### Mount path with dbt project (if you skip first command for cloning repository)
```shell
docker-compose run -v /local/path/to/your-dbt-project/:/code/data/dbt-project --rm app bin/console app:generate-profiles-and-sources
```

### app:run-dbt-command
```shell
docker-compose run --env-file .my-env --rm app bin/console app:run-dbt-command
```
Runs `dbt run` with DBT CLI (don't forget to pass environment variables to docker, in example above create file `.my-env` and paste them there). You can specify which models you want to run or leave blank if you want to run them all. You can also use your own local installation of DBT CLI if you want, probably somehow like `dbt run --profiles-dir ./profiles.yml` in path `/data/dbt-project/` (in that case you need to have exported environment variables to your local environment).

#### Mount path with dbt project (if you skip first command for cloning repository)
```shell
docker-compose run --env-file .my-env -v /local/path/to/your-dbt-project/:/code/data/dbt-project --rm app bin/console app:run-dbt-command
```

## Development
 
Clone this repository and init the workspace with following command:

```shell
git clone https://github.com/keboola/dbt-transformation.git
cd dbt-transformation
docker-compose build
docker-compose run --rm app composer install --no-scripts
```

Run the test suite using this command:

```shell
docker-compose run --rm app composer ci
```
 
# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/) 

## License

MIT licensed, see [LICENSE](./LICENSE) file.
