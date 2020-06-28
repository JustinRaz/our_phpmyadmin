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

    <style>
    .container{
        display: flex;
    }
    .fixed{
        width: 220px;
    }
    .flex-item{
        flex-grow: 1;
    }
    
    </style>
</head> 
<body>
    <h1>
        AdminForPHP
    </h1>

    <div class="container">

        <div id="db_list" class="fixed">
            <h3>Databases</h3>
            <?php
            $query_db_list = "SHOW DATABASES";
            $results_db_list = $connection->query($query_db_list);
            ?>

            <?php if ($results_db_list->num_rows > 0) : ?>
                <?php while($row = $results_db_list->fetch_assoc()): ?>
                    <li> <a class='nav-link active' href='?db=<?php echo $row["Database"]?>'><?php echo $row["Database"]?> </a></li>
                    <?php endwhile?>
            <?php endif?> 
        </div>

        <div id="db_body" class="flex-item">
            <?php if(isset($_GET["db"])):?>
                <h2><?php echo $_GET["db"]?></h2>
                <div id="crud_list">
                    <li>Create</li>
                    <li>Retrieve</li>
                    <li>Update</li>
                    <li>Delete</li>
                
                </div>
            <?php endif?>

        </div>

    </div>
</body>
</html>