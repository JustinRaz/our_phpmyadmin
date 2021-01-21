<?php
    session_start();

    $conn = new mysqli('localhost','root','');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if (isset($_POST['query'])){
        if ($conn->query($_POST['query']) == false){
            die($conn->error);
        }
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
                <form id="newdbform" action="./index.php" method="POST">
                    <label for="newdb">Add New Database:</label>
                        <input type="text" id="newdb" name="newdb"/>
                    <input type="button" id="createdb" value="Create Database">
                    <input type="hidden" id="query" name="query">
                </form>
                <h3>Databases</h3>
                <?php $count = 0 ?>
                <?php while ($row = $databases->fetch_row()): ?>
                    <li id="<?php echo $count++?>"class="list-group-item">
                        <a class="name" href="./Tables.php?database=<?php echo $row[0] ?>"><?php echo $row[0]?></a>
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
    <script>
        $(document).ready(function(){
            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                }
            });
            $("#createdb").on("click",function(){
                let newdb = $("#newdb").val();
                if (newdb == ''){
                    alert('Input is empty');
                }else {
                    let $existingDbs = $(".name");
                    console.log($existingDbs.eq(0).html())
                    for (var i=0 ; i<$existingDbs.length && newdb!=$existingDbs.eq(i).html() ; i++){}
                    if (i < $existingDbs.length){
                        alert('Another Database already has a similar name.');
                    }else {
                        $("#query").val(`CREATE DATABASE ${newdb}`);
                        $("#newdbform").submit();
                    }

                }
            });
        });
    </script>
</body>
</html>
