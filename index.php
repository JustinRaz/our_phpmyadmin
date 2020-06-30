<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";

$connection = mysqli_connect($servername, $username, $password);

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
                <?php if ($results_db_list->num_rows > 0) : ?>
                    <?php while($row = $results_db_list->fetch_assoc()): ?>
                        <li> <a class='nav-link active' href='?db=<?php echo $row["Database"]?>'><?php echo $row["Database"]?> </a></li>
                        <?php endwhile?>
                <?php endif?> 
            </ul>
        </div>

        <div id="db_body" class="flex-item">
            <?php if(isset($_GET["db"])):?>
                <h2><?php echo $_GET["db"]?></h2>
                <div id="crud_list">
                <ul>
                    <li>Select</li>
                </ul>
                
                </div>
            <?php endif?>

        </div>

    </div>
</body>
<script src="main.js"></script>
</html>