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
        <a href="./index.php ?>">Back</a>
        
        <h3>
            <?php echo $_GET['database'] ?>
        </h3>
        <a class="btn btn-secondary text-uppercase m-2" href="./CreateTableForm.php?database=<?php echo $_GET['database'] ?>">Create Table</a>
            <?php $count = 0 ?>
            <?php if(mysqli_num_rows($tables) != 0):?>
                <ul class="list-group">
                    <?php while ($row = $tables->fetch_row()) : ?>
                        <li id="<?php echo $count++?>" class="list-group-item">
                            <a class="m-2 float-left" href="./Rows.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $row[0] ?>"><?php echo $row[0]?></a>
                            <a class="m-2 float-right btn btn-primary" href="./UpdateTableForm.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $row[0] ?>">EDIT</a>
                            <a class="m-2 float-right btn btn-danger" href="./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $row[0] ?>&where=drop">DROP</a>
                            <a class="m-2 float-right btn btn-warning" href="./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $row[0] ?>&where=truncate">TRUNCATE</a>
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