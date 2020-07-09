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
<head><title>Tables</title></head>
<body>
    Tables in database <?php echo $_GET['database'] ?>
    <ul>
        <?php while ($row = $tables->fetch_row()) : ?>
            <li>
                <a href="./Rows.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $row[0] ?>"><?php echo $row[0]?></a>
                <a href="./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $row[0] ?>&where=drop">DROP</a>
                <a href="./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $row[0] ?>&where=truncate">TRUNCATE</a>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>