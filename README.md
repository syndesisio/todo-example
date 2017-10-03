# TODO App Example

This is a simple php application, which tracks todo items in a PostgreSQL DB.

## How to deploy

### CLI / oc Client

#### Create Application and expose Service
```
$ oc new-app https://github.com/syndesisio/todo-example.git --name=todo-example

$ oc expose service todo-example
```

