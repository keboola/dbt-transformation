# DBT transformation

> DBT transformation POC component

## CLI usage
You can use this component to run your DBT project locally using Keboola Snowflake Workspace as backend. For this purpose you can use following interactive CLI commands:
```
docker-compose run --rm dev bin/console app:clone-repository
docker-compose run --rm dev bin/console app:create-workspace
docker-compose run --rm dev bin/console app:generate-profiles-and-sources
docker-compose run --rm dev bin/console app:run-dbt-command
```
For first time you have to run `docker-compose build` before.

### app:clone-repository
Clones GIT repository with your DBT project. You input path to your repository when you are asked, branch what you want to use (leave blank if you want clone from master branch) and optionally GitHub credentials if repository is private. If you are using another GIT service than GitHub or you just want to clone your project manually, just skip this command and clone your project to folder `/data` and rename it `dbt-project`, so your project root will be in path `/data/dbt-project/`.

### app:create-workspace
Guides you to creating Snowflake Workspace in your Keboola project with input mapping for your DBT project. You will need your Keboola Connection URL and storage API token. If you have already created correctly setup workspace before, you can skip this command.

### app:create-workspace
Generates file `profiles.yml` with credentials to Snowflake Workspace and sources file with your input mapping based on your workspace. You will need your Keboola Connection URL, storage API token, workspace configuration ID (you know where to get it from previous command) and source name which you are using in you DBT project. Once you have these files generated, you are able to run DBT with your local installation or with next command `app:run-dbt-command`.

### app:run-dbt-command
Runs `dbt run` with DBT CLI. You can specify which models you want to run or leave blank if you want to run them all. You can also use your own local installation of DBT CLI if you want, probably somehow like `dbt run --profiles-dir ./.dbt/profiles.yml` in path `/data/dbt-project/`.


## Development
 
Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/dbt-transformation.git
cd dbt-transformation
docker-compose build
docker-compose run --rm dev composer install --no-scripts
```

Run the test suite using this command:

```
docker-compose run --rm dev composer tests
```
 
# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/) 
