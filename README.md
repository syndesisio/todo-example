# TODO App Example

This is an example php application, which tracks todo items.

## How to deploy

### CLI / oc Client

#### Create Application and expose Service
```
$ oc new-app https://github.com/syndesisio/todo-example.git --name=todo-example

$ oc expose service todo-example
```

