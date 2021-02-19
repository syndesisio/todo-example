<?php header('Content-Type: application/json') ?>
{
  "openapi": "3.0.0",
  "info": {
    "title": "Todo App API",
    "version": "1.0.0",
    "description": "Example Todo Application API",
    "license": {
      "name": "Apache 2.0",
      "url": "http://www.apache.org/licenses/LICENSE-2.0.html"
    }
  },
  "servers": [
    {
      "url": "http://<?php echo $_SERVER['HTTP_HOST'] ?>/api"
    },
    {
      "url": "https://<?php echo $_SERVER['HTTP_HOST'] ?>/api"
    }
  ],
  "paths": {
    "/": {
      "get": {
        "tags": [
          "tasks",
          "fetching"
        ],
        "responses": {
          "200": {
            "content": {
              "application/json": {
                "schema": {
                  "type": "array",
                  "items": {
                    "$ref": "#/components/schemas/Task"
                  }
                }
              }
            },
            "description": "All is good"
          }
        },
        "summary": "List all tasks",
        "description": "Fetches all tasks from the database"
      },
      "post": {
        "requestBody": {
          "description": "Task to create",
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/Task"
              }
            }
          },
          "required": true
        },
        "tags": [
          "tasks",
          "creating"
        ],
        "responses": {
          "201": {
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Task"
                }
              }
            },
            "description": "All is good"
          }
        },
        "summary": "Create new task",
        "description": "Stores new task in the database"
      }
    },
    "/{id}": {
      "get": {
        "tags": [
          "tasks",
          "fetching"
        ],
        "parameters": [
          {
            "name": "id",
            "description": "Task identifier",
            "schema": {
              "format": "int64",
              "type": "integer"
            },
            "in": "path",
            "required": true
          }
        ],
        "responses": {
          "200": {
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Task"
                }
              }
            },
            "description": "All is good"
          },
          "404": {
            "description": "No task with provided identifier found"
          }
        },
        "summary": "Fetch task",
        "description": "Fetches task by given identifier"
      },
      "put": {
        "requestBody": {
          "description": "Task with updates",
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/Task"
              }
            }
          },
          "required": true
        },
        "tags": [
          "tasks",
          "updating"
        ],
        "parameters": [
          {
            "name": "id",
            "description": "Task identifier",
            "schema": {
              "format": "int64",
              "type": "integer"
            },
            "in": "path",
            "required": true
          }
        ],
        "responses": {
          "200": {
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Task"
                }
              }
            },
            "description": "All is good"
          }
        },
        "summary": "Update task",
        "description": "Updates task by given identifier"
      },
      "delete": {
        "tags": [
          "tasks",
          "destruction"
        ],
        "parameters": [
          {
            "name": "id",
            "description": "Task identifier to delete",
            "schema": {
              "format": "int64",
              "type": "integer"
            },
            "in": "path",
            "required": true
          }
        ],
        "responses": {
          "204": {
            "description": "Task deleted"
          }
        },
        "summary": "Delete task",
        "description": "Deletes task by given identifier"
      }
    }
  },
  "components": {
    "schemas": {
      "Task": {
        "type": "object",
        "properties": {
          "id": {
            "format": "int64",
            "title": "Task ID",
            "description": "Unique task identifier",
            "type": "integer"
          },
          "task": {
            "title": "The task",
            "description": "Task line",
            "type": "string"
          },
          "completed": {
            "title": "Task completition status",
            "description": "0 - ongoing, 1 - completed",
            "maximum": 1,
            "minimum": 0,
            "type": "integer"
          }
        }
      }
    },
    "securitySchemes": {
      "username_password": {
        "scheme": "basic",
        "type": "http"
      }
    }
  }
}
