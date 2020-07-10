<?php
    session_start();

    $conn = new mysqli('localhost','root','');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $databases = $conn->query("SHOW DATABASES");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Databases</title>    
    <?php require "dependencies.php"?>
</head>
<body>
    <?php require "navbar.php"?>
    
    <div class="container">
        <div id="db-list-container" class="row">
            <ul id="db-list" class="list-group">
                <h3>Databases</h3>
                <?php $count = 0 ?>
                <?php while ($row = $databases->fetch_row()): ?>
                    <li id="<?php echo $count++?>"class="list-group-item">
                        <a href="./Tables.php?database=<?php echo $row[0] ?>"><?php echo $row[0]?></a>
                        <a id="drop-btn" class="btn btn-danger my-btn" style="display: none" href="./Delete.php?database=<?php echo $row[0] ?>&where=database">DROP</a>
                        <a id="query-join-btn" class="btn btn-success my-btn" style="display: none" href="./Query.php?database=<?php echo $row[0] ?>">QUERY / JOIN</a>
                    </li>
                <?php endwhile ?>
            </ul>
        </div>
    </div>
    <script src="main.js"></script>
</body>
</html>
