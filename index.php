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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <script 
        src="https://code.jquery.com/jquery-3.4.1.min.js" 
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" 
        crossorigin="anonymous">
    </script>

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
            <div class="row">
                <ul class="list-group">
                    <h3>Databases</h3>
                    <?php while ($row = $databases->fetch_row()): ?>
                        <li class="list-group-item">
                            <a type="button" class="btn btn-danger" href="./Delete.php?database=<?php echo $row[0] ?>&where=database">DROP</a>
                            <a href="./Query.php?database=<?php echo $row[0] ?>">Query/Join</a>
                            <a href="./Tables.php?database=<?php echo $row[0] ?>"><?php echo $row[0]?></a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        
        </div>
</body>
</html>
