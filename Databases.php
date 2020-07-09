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
<head><title>Databases</title></head>
<body>
    Databases
    <ul>
        <?php while ($row = $databases->fetch_row()) : ?>
            <li>
                <a href="./Tables.php?database=<?php echo $row[0] ?>"><?php echo $row[0]?></a>
                <a href="./Delete.php?database=<?php echo $row[0] ?>&where=database">DROP</a>
                <a href="./Query.php?database=<?php echo $row[0] ?>">Query/Join</a>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
