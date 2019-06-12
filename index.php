<?php
  require __DIR__ . '/vendor/autoload.php';

  use Stomp\Client;
  use Stomp\Exception\StompException;
  use Stomp\Stomp;

  $DB_SERVER="127.0.0.1";
  $DB_NAME="todo";
  $DB_TABLE_NAME="todo";
  $DB_SCHEMA="";
  $DB_USER="test";
  $DB_PASS="test";
  $AMQ_USER="amq";
  $AMQ_PASS="topSecret";
  $AMQ_DEFAULT_MESSAGE = <<< "EOD"
<?xml version="1.0" encoding="UTF-8"?>
<inventoryReceived>
  <item id="XYZ123" damaged="false" vendor="Good Inc."/>
  <item id="ABC789" damaged="true" vendor="Bad Inc."/>
</inventoryReceived>
EOD;

  if ( $_ENV["TODO_DB_SERVER"] ) {
    $DB_SERVER = $_ENV["TODO_DB_SERVER"];
  }
  if ( $_ENV["TODO_DB_NAME"] ) {
    $DB_NAME = $_ENV["TODO_DB_NAME"];
  }
  if ( $_ENV["TODO_DB_SCHEMA"] ) {
    $DB_SCHEMA = $_ENV["TODO_DB_SCHEMA"];
    $DB_SCHEMA = $DB_SCHEMA.".";
  }
  if ( $_ENV["TODO_DB_USER"] ) {
    $DB_USER = $_ENV["TODO_DB_USER"];
  }
  if ( $_ENV["TODO_DB_PASS"] ) {
    $DB_PASS = $_ENV["TODO_DB_PASS"];
  }
  if ( $_ENV["TODO_AMQ_USER"] ) {
    $AMQ_USER = $_ENV["TODO_AMQ_USER"];
  }
  if ( $_ENV["TODO_AMQ_PASS"] ) {
    $AMQ_PASS = $_ENV["TODO_AMQ_PASS"];
  }
  $connection = pg_connect("host=".$DB_SERVER." dbname=".$DB_NAME." user=".$DB_USER." password=".$DB_PASS);
  if ( !$connection ) {
    die("Database connection failed: " . pg_last_error());
  }

  $result = pg_query($connection, "CREATE TABLE IF NOT EXISTS ".$DB_TABLE_NAME." (id SERIAL PRIMARY KEY, task VARCHAR, completed INTEGER);");
  if ( !$result ) {
    die("Could not create tables.");
  }

  # Check for task
  if ( isset( $_POST['action'] ) && $_POST['action'] !== "" ) {
    $result = false;
    if( $_POST['action'] == "add" ) {
      $data = array("task"=>$_POST['task'], "completed"=>"0");
      $result = pg_insert($connection , $DB_SCHEMA.$DB_TABLE_NAME, $data);
    } else if( $_POST['action'] == "update" ) {
      $data = array("task"=>$_POST['task']);
      $condition = array("id"=>$_POST['id'],);
      $result = pg_update($connection , $DB_SCHEMA.$DB_TABLE_NAME, $data, $condition);
    } else if( $_POST['action'] == "check" ) {
      $condition = array("id"=>$_POST['id'],);
      $selected = pg_select($connection , $DB_SCHEMA.$DB_TABLE_NAME, $condition);
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
      $result = pg_update($connection , $DB_SCHEMA.$DB_TABLE_NAME, $data, $condition);
    } else if( $_POST['action'] == "delete" ) {
      $condition = array("id"=>$_POST['id'],);
      $result = pg_delete($connection , $DB_SCHEMA.$DB_TABLE_NAME, $condition);
    }
    if ( !$result ) {
      die("Insert failed: " . pg_last_error());
    }
    header( 'Location: index.php');
  }

  if ( isset( $_POST['amq'] ) && $_POST['amq'] == "send" ) {
    $amq_result = "";
    $stomp = new Client('tcp://broker-amq-tcp:61613');
    $stomp->setLogin($AMQ_USER, $AMQ_PASS);
    try {
      $stomp->connect();
      $message = trim($_POST['message']);
      $stomp->send('/queue/inventoryReceived', $message);
      $amq_result = "success";
    } catch (StompException $e) {
      die("Failed to send message: " . $e);
    } finally {
      $stomp->disconnect();
    }

    header( 'Location: index.php?amqMsg=' .urlencode($amq_result) );
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

    <title>Todo App</title>

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

    <style type="text/css">
      .form-inline .form-group  {
        display: flex;
        width: 100%;
      }
      .form-control {
        flex:1;
      }
      .ml {
        margin-left:5px;
      }
    </style>
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
      <div id="jmsToggle" class="row">
        <div class="col-md-8 col-md-offset-2">
          <ul class="list-group">
              <li class='list-group-item'>
                <div class='form-group'>
                  <button class='ml btn btn-primary' id="toggleForm">
                    <span>Show JMS Form</span>
                  </button>
                  <?php
                    if ( isset ( $_GET['amqMsg'] ) && $_GET['amqMsg'] == "success" ) {
                      echo "  <span>Message sent successfully</span>\n";
                    }
                  ?>
                </div>
              </form>
              </li>
          </ul>
        </div>
      </div>
      <div id="jmsForm" class="row" style="display:none">
        <div class="col-md-8 col-md-offset-2">
          <ul class="list-group">
              <li class='list-group-item'>
                <form class='form-inline' method='post'>
                <div class='form-group'>
                  <textarea class='ml form-control' rows='6' name='message'><?php
                      echo $AMQ_DEFAULT_MESSAGE;
                  ?></textarea>

                </div><br />
                <div class='form-group'>
                  <button class='ml btn btn-primary' name='amq' value='send'>
                    <span>Send JMS Message</span>
                  </button>
                </div>
              </form>
              </li>
          </ul>
        </div>
    </div>
      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <ul class="list-group">



<?php

  $result = pg_query($connection, "SELECT id, task, completed FROM ".$DB_SCHEMA.$DB_TABLE_NAME." order by id");
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
        echo "        <input class='ml form-control' name='task' value='", $row[1], "' placeholder='Enter TODO item...' />";
        echo "        <button class='ml btn btn-primary' name='action' value='update'>";
        echo "            <span>Update</span>";
        echo "        </button>";
        echo "        <button class='ml btn btn-danger' name='action' value='delete'>";
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
                <button class="ml btn btn-success" name='action' value='add'>
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
    <script type="text/javascript">
      $(function() {
        $('#toggleForm').click(function() {
          $("#jmsForm").toggle( "fast");
          $("#jmsToggle").toggle( "fast");
        });
      });
    </script>
  </body>
</html>
