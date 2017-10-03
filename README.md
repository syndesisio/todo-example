# TODO App Example

This is a simple php application, which tracks todo items in a PostgreSQL DB.

## How to deploy

### CLI / oc Client

#### Create Application and expose Service
```
# Create the DB for the app..
$ oc new-app openshift/postgresql-92-centos7 \
	--name=postgres \
    -e POSTGRESQL_DATABASE=todo \
    -e POSTGRESQL_USER=test \
    -e POSTGRESQL_PASSWORD=password

# Spin up the app.
$ oc new-app https://github.com/syndesisio/todo-example.git \
    --name=todo \
    -e TODO_DB_SERVER=postgres \
    -e TODO_DB_USER=test \
    -e TODO_DB_PASS=password 

# Create a route that you can access
$ oc expose service todo
$ oc get routes todo
```

