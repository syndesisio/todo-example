<?php
  $DB_SERVER="127.0.0.1";
  $DB_NAME="todo";
  $DB_USER="test";
  $DB_PASS="test";

  if ( $_ENV["TODO_DB_SERVER"] ) {
    $DB_SERVER = $_ENV["TODO_DB_SERVER"];
  }
  if ( $_ENV["TODO_DB_NAME"] ) {
    $DB_NAME = $_ENV["TODO_DB_NAME"];
  }
  if ( $_ENV["TODO_DB_USER"] ) {
    $DB_USER = $_ENV["TODO_DB_USER"];
  }
  if ( $_ENV["TODO_DB_PASS"] ) {
    $DB_PASS = $_ENV["TODO_DB_PASS"];
  }
  $connection = pg_connect("host=".$DB_SERVER." dbname=".$DB_NAME." user=".$DB_USER." password=".$DB_PASS);
  if ( !$connection ) {
    die("Database connection failed: " . pg_last_error());
  }

  $method = $_SERVER["REQUEST_METHOD"];
  $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
  $id_str = substr($path, strrpos($path, "/api") + 5);
  $has_id = "" != $id_str;
  $id = intval($id_str, 10);

  $result = false;
  $data = array();
  switch ($method) {
    case "GET":
      if ($has_id) {
        $data = pg_select($connection , "todo", array("id" => $id));
        if (sizeof($data) > 0) {
            $data = $data[0];
            $result = TRUE;
        } else {
            http_response_code(404);
        }
      } else {
        $data = pg_fetch_all(pg_query($connection, "SELECT id, task, completed FROM todo"));
        $result = TRUE;
      }
      break;
    case "PUT":
      $data = json_decode(file_get_contents("php://input"), true);
      $result = pg_update($connection , "todo", $data, array("id" => $id));
      $data = array();
      break;
    case "POST":
      $given = json_decode(file_get_contents("php://input"), true);
      $data = array("task" => $given["task"], "completed" => (int)$given["completed"]);
      $result = pg_query_params($connection, "INSERT INTO todo (task, completed) VALUES ($1, $2) RETURNING id", array($data["task"], $data["completed"]));
      if ($result) {
        $data = array("id" => pg_fetch_array($result)["id"], "task" => $given["task"], "completed" => $given["completed"]);
        http_response_code(201);
      }
      break;
    case "DELETE":
      $condition = array("id" => $id);
      $result = pg_delete($connection, "todo", $condition);
      http_response_code(204);
      break;
  }

  if ($result) {
    if (empty($data)) {
      http_response_code(404);
    } else {
      header('Content-Type: application/json');
      echo json_encode($data);
    }
  } else {
    http_response_code(500);
    echo json_encode(array("error" => pg_last_error()));
  }
?>
