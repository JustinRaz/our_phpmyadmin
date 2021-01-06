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
                        <span id="drop_form">
                            <button id="drop_btn_modal"class="btn btn-danger" style="display: none" type="button"  data-toggle="modal" data-target="#drop_modal_<?php echo $count?>">
                                DROP
                            </button>
                            <div class="modal fade" id="drop_modal_<?php echo $count?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Warning!</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete <strong><?php echo $row[0]?></strong>.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCEL</button>                                         
                                            <a id="drop-btn" class="btn btn-danger my-btn" href="./Delete.php?database=<?php echo $row[0] ?>&where=database">DROP</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </span>
                        <a id="query-join-btn" class="btn btn-success my-btn" style="display: none" href="./Query.php?database=<?php echo $row[0] ?>">QUERY / JOIN</a>
                    </li>
                <?php endwhile ?>
            </ul>
        </div>
    </div>
    <script src="main.js"></script>
</body>
</html>
