<?php
    session_start();

    $conn = new mysqli('localhost','root','');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $tables = $conn->query("SHOW TABLES FROM {$_GET['database']}");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tables</title>
    
    <?php require "dependencies.php"?>

</head>
<body>
    <?php require "navbar.php"?>

    <div class="container">
        <h3>
            <?php echo $_GET['database'] ?>
        </h3>
            <?php if(mysqli_num_rows($tables) != 0):?>
                <ul>
                    <?php while ($row = $tables->fetch_row()) : ?>
                        <li>
                            <a href="./Rows.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $row[0] ?>"><?php echo $row[0]?></a>
                            <a href="./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $row[0] ?>&where=drop">DROP</a>
                            <a href="./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $row[0] ?>&where=truncate">TRUNCATE</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    The database is empty.
                </div>
            <?php endif?>
        
    </div>
</body>
</html>