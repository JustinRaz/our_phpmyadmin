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
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">PhpOurAdmin</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    
    <div class="container">
        <div id="db-list-container" class="row">
            <ul id="db-list" class="list-group">
                <h3>Databases</h3>
                <?php $count = 0 ?>
                <?php while ($row = $databases->fetch_row()): ?>
                    <li id="<?php echo $count++?>"class="list-group-item">
                        <a href="./Query.php?database=<?php echo $row[0] ?>">Query/Join</a>
                        <a href="./Tables.php?database=<?php echo $row[0] ?>"><?php echo $row[0]?></a>
                        <a id="drop-btn" type="button" class="btn btn-danger my-btn" style="display: none" href="./Delete.php?database=<?php echo $row[0] ?>&where=database">DROP</a>
                    </li>
                <?php endwhile ?>
            </ul>
        </div>
    </div>
    <script src="main.js"></script>
</body>
</html>
