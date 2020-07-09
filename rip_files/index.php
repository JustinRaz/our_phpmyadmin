<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";

// $_GET[] VARIABLES ESTABLISHED
// 1. db - indicates current db
// 2. table - indicates current db

if(isset($_GET["db"])){
    $connection = mysqli_connect($servername, $username, $password, $_GET["db"]);
}else{
    $connection = mysqli_connect($servername, $username, $password);
}

if(!$connection){
    die("Connection failed: ".$connection->connect_error);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>AdminForPhp</title>
    <link rel="stylesheet" href="style.css">
    <script 
        src="https://code.jquery.com/jquery-3.4.1.min.js" 
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" 
        crossorigin="anonymous">
    </script>
</head> 
<body>
    <h1>
        AdminForPHP
    </h1>

    <div class="container">

        <div id="db_list_container" class="fixed">
            <?php if(isset($_GET["db"])):?>
                <button><h3 id="db_list_title">DB</h3></button>
            <?php else:?>
                <h3 id="db_list_title">Databases</h3>
            <?php endif?>
          
            <ul id="db_list" 
                <?php if(isset($_GET["db"])):?>
                    style="display: none"
                <?php endif?>
            >
                <?php
                $query_db_list = "SHOW DATABASES";
                $results_db_list = $connection->query($query_db_list);
                ?>
                <li>
                <!-- FORM 1 -->
                    <form action="" method="post">
                        <input type="text" name="" placeholder="Add database">
                        <input type="submit" value="+">
                    </form>
                </li>
                <?php if ($results_db_list->num_rows > 0) : ?>
                    <?php while($row = $results_db_list->fetch_assoc()): ?>
                        <li> <a class='nav-link active' href='?db=<?php echo $row["Database"]?>'><?php echo $row["Database"]?> </a></li>
                        <?php endwhile?>
                <?php endif?> 
            </ul>
        </div>

        <?php if(isset($_GET["db"])):?>
            <div id="db_body" class="flex-item">
                <div id="db_tables">
                    <?php
                    $query_db_table_list = "SHOW tables";
                    $results_db_table_list = $connection->query($query_db_table_list);
                    ?>
                    <?php if($results_db_table_list->num_rows==0):?>
                        <h2>Database is empty.</h2>
                    <?php else:?>
                        <h2>Tables</h2>
                    <?php endif?>
                    <ul id="db_table_list">
                        <li>
                        <!-- FORM 2 -->
                            <form action="" method="post">
                                <input type="text" name="" placeholder="Add table">
                                <input type="submit" value="+">
                            </form>
                        </li>
                        <?php if ($results_db_table_list->num_rows > 0): ?>
                            <?php while($row = $results_db_table_list->fetch_assoc()): ?>
                                <li><a class='nav-link active' href='?db=<?php echo $row["Tables_in_".$_GET['db']]?>'><?php echo $row["Tables_in_".$_GET['db']]?> </a></li>
                            <?php endwhile?>
                        <?php endif?> 
                    </ul>
                </div>

                <div id="db_operations">
                        <h2><?php echo $_GET["db"]?></h2>
                        <div id="crud_list">
                        <ul>
                            <li>Select</li>
                        </ul>
                        
                        </div>
                </div>

            </div>
        <?php endif?>

    </div>
</body>
<script src="main.js"></script>
</html>