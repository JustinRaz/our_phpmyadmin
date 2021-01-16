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
        <a href="./CreateTableForm.php?database=<?php echo $_GET['database'] ?>" class="btn btn-secondary text-uppercase">Create Table</a>
            <?php if(mysqli_num_rows($tables) != 0):?>
                <ul class="list-group">
                    <?php while ($row = $tables->fetch_row()) : ?>
                        <li class="list-group-item">
                            <a href="./Rows.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $row[0] ?>" class="m-2 float-left"><?php echo $row[0]?></a>
                            <a href="./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $row[0] ?>&where=drop" class="btn btn-danger float-right m-2">DROP</a>
                            <a href="./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $row[0] ?>&where=truncate" class="btn btn-primary float-right m-2">TRUNCATE</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    The database is empty.
                </div>
                <div class="alert alert-success" role="alert">
                    <h4>Insert a new Table</h4>
                </div>
            <?php endif?>
        
    </div>
</body>
</html>