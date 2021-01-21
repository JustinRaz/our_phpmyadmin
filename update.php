<?php
    session_start();

    $databaseUrl = empty($_GET['database']) ? NULL : $_GET['database'];
    
    // updateVal is an array of objects so it looks like this:
    // [{column: 'name', value: 'John Roy'}, {column: 'course', value: 'Computer Science'}]

    $conn = new mysqli('localhost', 'root', '', $databaseUrl);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $updateVals = $_GET['updateValues'];
    $table = $_GET['table'];
    // condition looks like this
    // {column: 'year', clause: '=', value: '3'}
    $cond = $_GET['condition'];
    $sql = 'UPDATE '.$table.' SET';
    $total = count($updateVals);
    $index = 0;
    
    foreach ($updateVals as $update) {
      $sql .= ' '.$update->column.' = \''.$update->value.'\'';
      $index++;
      if($index < $total) {
        $sql .= ',';
      }
    }
    
    $sql .= ' WHERE '.$cond->column.' '.$cond->clause.' \''.$cond->value.'\'';
    
    $conn->query($sql);
?>