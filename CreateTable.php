<?php
    session_start();

    $conn = new mysqli('localhost','root','');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($conn->query($_POST['query']) == false){
        echo $conn->error;
    }else {
        header("Location: ./tables.php?database={$_POST['database']}");
    }
    $conn->close();
?>