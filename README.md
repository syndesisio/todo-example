# TODO App Example

This is a simple php application, which tracks todo items in a PostgreSQL DB.

## How to deploy

### CLI / oc Client

#### Create Application and expose Service
```
$ oc new-app https://github.com/syndesisio/todo-example.git \
    --name=todo-example \
    -e TODO_DB_SERVER=postgres \
    -e TODO_DB_USER=test \
    -e TODO_DB_PASS=password 

$ oc expose service todo-example
```

