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

  $result = pg_query($connection, "CREATE TABLE IF NOT EXISTS  todo (id SERIAL PRIMARY KEY, task VARCHAR, completed INTEGER);");
  if ( !$result ) {
    die("Could not create tables.");
  }

  # Check for task
  if ( isset( $_POST['action'] ) && $_POST['action'] !== "" ) {
    $result = false;
    if( $_POST['action'] == "add" ) {
      $data = array("task"=>$_POST['task'], "completed"=>"0");
      $result = pg_insert($connection , "todo", $data);
    } else if( $_POST['action'] == "update" ) {
      $data = array("task"=>$_POST['task']);
      $condition = array("id"=>$_POST['id'],);
      $result = pg_update($connection , "todo", $data, $condition);
    } else if( $_POST['action'] == "check" ) {
      $condition = array("id"=>$_POST['id'],);
      $selected = pg_select($connection , "todo", $condition);
      $data = array();
      if( $selected ) {
        $data = $selected[0];
        if ( $data["completed"]==1 ) {
          $data["completed"]=0;
        } else {
          $data["completed"]=1;
        }
      }
      $data["task"]=$_POST['task'];
      $result = pg_update($connection , "todo", $data, $condition);
    } else if( $_POST['action'] == "delete" ) {
      $condition = array("id"=>$_POST['id'],);
      $result = pg_delete($connection , "todo", $condition);
    }
    if ( !$result ) {
      die("Insert failed: " . pg_last_error());
    }
    header( 'Location: index.php');
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>APPUiO PHP Demo</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/starter-template.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">Todos</a>
        </div>
      </div>
    </nav>

    <br>
    <br>
    <div class="container">
      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <ul class="list-group">



<?php

  $result = pg_query($connection, "SELECT id, task, completed FROM todo order by id");
  while ( $row = pg_fetch_array($result) ) {
        echo "    <li class='list-group-item'>";
        echo "    <form class='form-inline' method='post'>";
        echo "        <input type='hidden' name='id' value='", $row[0], "'/>";
        echo "        <div class='form-group'>";
        echo "        <button class='btn btn-success' name='action' value='check'>";
        if( $row[2]==1 ) {
          echo "         <span class='glyphicon glyphicon-ok'></span>";
        } else {
          echo "         <span class='glyphicon'>&nbsp;</span>";
        }
        echo "        </button>";
        echo "        <input class='form-control' name='task' value='", $row[1], "' placeholder='Enter TODO item...' />";
        echo "        <button class='btn btn-primary' name='action' value='update'>";
        echo "            <span>Update</span>";
        echo "        </button>";
        echo "        <button class='btn btn-danger' name='action' value='delete'>";
        echo "            <span>Delete</span>";
        echo "        </button>";
        echo "    </div>";
        echo "    </form>";
        echo "    </li>";
  }
?>
          <li class="list-group-item">
            <form class="form-inline" method='post'>
              <div class="form-group">
                <input class="form-control" name='task' value='' placeholder="Enter TODO item..."/>
                <button class="btn btn-success" name='action' value='add'>
                    <span>Add</span>
                </div>
              </div>
            </form>
          </li>
          </ul>

        </div>
      </div>

    </div><!-- /.container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery.min.js"><\/script>')</script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
