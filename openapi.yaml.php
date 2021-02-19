<?php header('Content-Type: application/yaml') ?>
openapi: 3.0.0
info:
  title: Todo App API
  description: Example Todo Application API
  version: 1.0.0
  license:
    name: Apache 2.0
    url: http://www.apache.org/licenses/LICENSE-2.0.html
paths:
  /:
    get:
      tags:
        - tasks
        - fetching
      summary: List all tasks
      description: Fetches all tasks from the database
      responses:
        "200":
          description: All is good
          content:
            application/json:
              schema:
                type: array
                items:
                  $ref: "#/components/schemas/Task"
    post:
      tags:
        - tasks
        - creating
      summary: Create new task
      description: Stores new task in the database
      requestBody:
        content:
          application/json:
            schema:
              $ref: "#/components/schemas/Task"
        description: Task to create
        required: true
      responses:
        "201":
          description: All is good
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/Task"
  "/{id}":
    get:
      tags:
        - tasks
        - fetching
      summary: Fetch task
      description: Fetches task by given identifier
      parameters:
        - in: path
          name: id
          description: Task identifier
          required: true
          schema:
            type: integer
            format: int64
      responses:
        "200":
          description: All is good
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/Task"
        "404":
          description: No task with provided identifier found
    put:
      tags:
        - tasks
        - updating
      summary: Update task
      description: Updates task by given identifier
      parameters:
        - in: path
          name: id
          description: Task identifier
          required: true
          schema:
            type: integer
            format: int64
      requestBody:
        content:
          application/json:
            schema:
              $ref: "#/components/schemas/Task"
        description: Task with updates
        required: true
      responses:
        "200":
          description: All is good
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/Task"
    delete:
      tags:
        - tasks
        - destruction
      summary: Delete task
      description: Deletes task by given identifier
      parameters:
        - in: path
          name: id
          description: Task identifier to delete
          required: true
          schema:
            type: integer
            format: int64
      responses:
        "204":
          description: Task deleted
servers:
  - url: http://<?php echo $_SERVER['HTTP_HOST'] ?>/api
  - url: https://<?php echo $_SERVER['HTTP_HOST'] ?>/api
components:
  securitySchemes:
    username_password:
      type: http
      scheme: basic
  schemas:
    Task:
      type: object
      properties:
        id:
          type: integer
          format: int64
          title: Task ID
          description: Unique task identifier
        task:
          type: string
          title: The task
          description: Task line
        completed:
          type: integer
          title: Task completition status
          description: 0 - ongoing, 1 - completed
          minimum: 0
          maximum: 1
