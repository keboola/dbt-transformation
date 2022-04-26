
# DBT transformation

## Configuration

The configuration `config.json` contains following properties in `parameters` key:
- `git` - object (required): Configuration of repository with DBT project.
    - `repo` - string (required): URL of GitHub repository with your DBT project.
    - `username` - string (optional): GitHub username if repository is private.
    - `password` - string (optional): GitHub Private Access Token if repository is private. Both or none of couple `username` and `password` must be specified.
    - `branch` - string (optional): Specify git branch if you want to clone project from specific branch.
- `dbt` - object (required): Configuration of DBT
    - `generateSources` - boolean (required): 
      - If `true` sources YAML file is generated. You should probably use it if running DBT project for first time in Keboola.
      - If `false` generating of sources file is skipped. Use it if you have already correctly setup sources file in your DBT project.
    - `sourceName` - string (required if `generateSources` set to `true`): Set source name which should be used in sources YAML file. It has to be same name as you use in DBT [`{{ source() }}` function](https://docs.getdbt.com/reference/dbt-jinja-functions/source).
    - `modelNames` - array of strings (optional): If you want to run DBT only with certain models, you can specify their names here. Otherwise, all models will be run.
- `showExecutedSqls` - boolean (optional): Default `false`, if set to `true` SQL queries executed by DBT transformation are printed to output.

## CLI usage


You can use this component to run your DBT project locally using Keboola Snowflake Workspace as backend. Before using following CLI commands, clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/dbt-transformation.git
cd dbt-transformation
docker-compose run --rm cli composer install --prefer-dist --no-interaction --no-dev
```


When preparing the repository, you can use following interactive CLI commands:
```
docker-compose run --rm cli bin/console app:clone-repository
docker-compose run --rm cli bin/console app:create-workspace
docker-compose run --rm cli bin/console app:generate-profiles-and-sources
docker-compose run --rm cli bin/console app:run-dbt-command
```
For first time you have to run `docker-compose build cli` before.

### app:clone-repository
Clones GIT repository with your DBT project. You input path to your repository when you are asked, branch what you want to use (leave blank if you want clone from master branch) and optionally GitHub credentials if repository is private. If you are using another GIT service than GitHub or you just want to clone your project manually, just skip this command and clone your project to folder `/data` and rename it `dbt-project`, so your project root will be in path `/data/dbt-project/`.

### app:create-workspace
Guides you to creating Snowflake Workspace in your Keboola project with input mapping for your DBT project. You will need your Keboola Connection URL and storage API token. If you have already created correctly setup workspace before, you can skip this command.

### app:generate-profiles-and-sources
Generates file `profiles.yml` with credentials to Snowflake Workspace and sources file with your input mapping based on your workspace. You will need your Keboola Connection URL, storage API token, workspace configuration ID (you know where to get it from previous command) and source name which you are using in you DBT project. Once you have these files generated, you are able to run DBT with your local installation or with next command `app:run-dbt-command`.

### app:run-dbt-command
Runs `dbt run` with DBT CLI. You can specify which models you want to run or leave blank if you want to run them all. You can also use your own local installation of DBT CLI if you want, probably somehow like `dbt run --profiles-dir ./.dbt/profiles.yml` in path `/data/dbt-project/`.


## Development
 
Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/dbt-transformation.git
cd dbt-transformation
docker-compose build
docker-compose run --rm app composer install --no-scripts
```

Run the test suite using this command:

```
docker-compose run --rm app composer ci
```
 
# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/) 
