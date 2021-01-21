<?php
    session_start();

    if(isset($_POST['submit'])){
        $con = mysqli_connect("localhost","root","",$_GET['database']);
        if (!$con){
            die ('Could not connect:' . mysqli_error());
        }
        $_SESSION['sql']="insert into ".$_POST['database'].".".$_POST['table']." (";
        $result = $con->query("SELECT `COLUMN_NAME`  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$_GET['database']."' AND TABLE_NAME = '".$_GET['table']."'");
        $res = $con->query("SELECT `COLUMN_NAME`,DATA_TYPE  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$_GET['database']."' AND TABLE_NAME = '".$_GET['table']."'");
        if($result->num_rows>0){
            $cnt=$result->num_rows;
            $i=0;
            while($row = $result->fetch_assoc()){
                $_SESSION['sql'].=$row['COLUMN_NAME'];
                $i++;
                if($i!=$cnt){
                    $_SESSION['sql'].=", ";
                }
            }
            $_SESSION['sql'].=") VALUES(";
            $i=0;
            while($row = $res->fetch_assoc()){
                if($_POST[$row['COLUMN_NAME']]!=""){
                    if($row['DATA_TYPE']=="char" || $row['DATA_TYPE']=="varchar" || $row['DATA_TYPE']=="tinytext" ||
                        $row['DATA_TYPE']=="text" || $row['DATA_TYPE']=="blob" || $row['DATA_TYPE']=="mediumtext" ||
                        $row['DATA_TYPE']=="mediumblob" || $row['DATA_TYPE']=="longblob" || $row['DATA_TYPE']=="longtext" ||
                        $row['DATA_TYPE']=="enum" || $row['DATA_TYPE']=="set" || $row['DATA_TYPE']=="bool" || $row['DATA_TYPE']=="boolean"){
                            $_SESSION['sql'].="'".$_POST[$row['COLUMN_NAME']]."'";
                    }elseif($row['DATA_TYPE']=="tinyint" || $row['DATA_TYPE']=="smallint" || $row['DATA_TYPE']=="mediumint" ||
                        $row['DATA_TYPE']=="int" || $row['DATA_TYPE']=="bigint"){
                            // if(is_int($_POST[$row['COLUMN_NAME']])){
                                //FSR dli niya kwaon
                                $_SESSION['sql'].=$_POST[$row['COLUMN_NAME']];
                            // }
                    }elseif($row['DATA_TYPE']=="float" || $row['DATA_TYPE']=="double" || $row['DATA_TYPE']=="decimal"){
                            if(is_float($_POST[$row['COLUMN_NAME']])){
                                //not verified, I dont use float
                                $_SESSION['sql'].=$_POST[$row['COLUMN_NAME']];
                            }
                    }else{
                    //     $_SESSION['sql'].=$_POST[$row['COLUMN_NAME']];
                    }
                    //no date, datetime, time and timestamp
                }else{
                    $_SESSION['sql'].="NULL";
                }
                $i++;
                if($i!=$cnt){
                    $_SESSION['sql'].=", ";
                }
            }
            $_SESSION['sql'].=")";
            if ($con->query($_SESSION['sql']) === TRUE) {
                echo "<div class='m-2 alert alert-success'>New record created successfully</div>";
              } else {
                echo "Error: " . $_SESSION['sql'] . "<br>" . $con->error;
              }
              
              $con->close();
        }
    }
?>
<!DOCTYPE html>
<head>
    <title>Insertion</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="style.css" />
    
</head>
<body>

    <?php require "navbar.php"?>

    <div class="container">
        <a href="./Rows.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $_GET['table'] ?>">Back</a>
        
        <div class="row">
            <div class="col-2 border border-dark">
                <h3>Databases</h3>
                <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
                    <?php
                        $sql="SHOW DATABASES";
                        $link = mysqli_connect('localhost','root','') or die ('Error connecting to mysql: ' . mysqli_error($link).'\r\n');
                        
                        if (!($result=mysqli_query($link,$sql))){
                                printf("Error: %s\n", mysqli_error($link));
                        }
                        
                        while( $row = mysqli_fetch_row( $result ) ){
                            if (($row[0]!="information_schema") && ($row[0]!="mysql")) {
                                echo "<div class=\"form-check\">
                                    <input class=\"form-check-input\" onchange=\"this.form.submit();\" type=\"radio\" name=\"database\" value=\"".$row[0]."\"";
                                if(isset($_GET['database']) && $_GET['database']==$row[0]){
                                    echo " checked";
                                }
                                echo "\">
                                    <label class=\"form-check-label\" for=\"".$row[0]."\">"
                                    .$row[0].
                                    "</label>
                                </div>";
                            }
                        }
                    ?>
                </form>
            </div>
            <div class="col-2 border border-dark">
                <?php if(isset($_GET) && isset($_GET['database'])):?>
                    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
                    <h3><?php echo $_GET['database'];?></h3>
                    <input type="hidden" name="database" value="<?php echo htmlspecialchars($_GET['database']);?>">
                        <?php
                            $con = mysqli_connect("localhost","root","",$_GET['database']);
                            if (!$con)
                            {
                            die ('Could not connect:' . mysqli_error());
                            }

                            $showtables= mysqli_query($con, "SHOW TABLES FROM ".$_GET['database']);

                            while($row = mysqli_fetch_array($showtables)) { 
                                echo "<div class=\"form-check\">
                                    <input class=\"form-check-input\" onchange=\"this.form.submit();\" type=\"radio\" name=\"table\" value=\"".$row[0]."\"";
                                if(isset($_GET['table']) && $_GET['table']==$row[0]){
                                    echo " checked";
                                }
                                echo "\">
                                    <label class=\"form-check-label\" for=\"".$row[0]."\">"
                                    .$row[0].
                                    "</label>
                                </div>";
                            }
                        ?>
                    </form>
                <?php else:?>
                    <h3>Select Database</h3>         
                <?php endif?>
            </div>
            <div class="col-8 border border-dark pb-2">
                <?php if(isset($_GET) && isset($_GET['database'])):?>
                    <?php if(isset($_GET['table'])):?>
                        <!-- <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post"> -->
                        <form action="insertion_v2.php?database=<?php echo $_GET['database']?>&table=<?php echo $_GET['table']?>" method="post">
                        <input type="hidden" name="database" value="<?php echo htmlspecialchars($_GET['database']);?>">
                        <input type="hidden" name="table" value="<?php echo htmlspecialchars($_GET['table']);?>">
                            <h3><?php echo $_GET['table'];?></h3>
                            <table class="table">
                                <tr><th>Name</th><th>Type</th><th>Nullable</th><th>Value</th></tr>
                                <?php
                                    $conn = new mysqli('localhost', 'root', '');

                                    // Check connection
                                    if ($conn->connect_error) {
                                    die("Connection failed: " . $conn->connect_error);
                                    }
                                    // echo "Connected successfully";

                                    $result = $conn->query("SELECT `COLUMN_NAME`,COLUMN_TYPE ,COLUMN_KEY ,IS_NULLABLE ,COLUMN_DEFAULT ,EXTRA ,DATA_TYPE ,CHARACTER_MAXIMUM_LENGTH  FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$_GET['database']."' AND TABLE_NAME = '".$_GET['table']."'");
                                    if($result->num_rows>0){
                                        while($row = $result->fetch_assoc()){
                                            echo "<tr><td class=\" border border-dark\">".$row['COLUMN_NAME']."</td>
                                                <td class=\" border border-dark\">".$row['COLUMN_TYPE']."</td><td class=\"border border-dark\">";
                                            if($row['IS_NULLABLE']=="YES"){
                                                echo "YES";
                                            }elseif($row['EXTRA']=="auto_increment"){
                                                echo "Auto Increment";
                                            }else{
                                                echo "NO";
                                            }
                                            echo "</td><td class=\" border border-dark\">";
                                            $sql2="SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='".$_GET['database']."' and `REFERENCED_TABLE_NAME`!='' and TABLE_NAME='".$_GET['table']."' and COLUMN_NAME='".$row['COLUMN_NAME']."'";
                                            $res2=$conn->query($sql2);                                        
                                            if($res2->num_rows>0){
                                                $row2=$res2->fetch_assoc();
                                                echo "<div class=\"form-group w-75\"><select class=\"form-control\" name=\"".$row['COLUMN_NAME']."\"><option selected disabled>Choose...</option>";
                                                    $sql3="SELECT * FROM ".$row2['REFERENCED_TABLE_SCHEMA'].".".$row2['REFERENCED_TABLE_NAME'];
                                                    $result3 = $conn->query($sql3);
                                                    if($result3->num_rows>0){
                                                        while($row3=$result3->fetch_assoc()){
                                                            echo "<option value=".$row3['id'].">".$row3['id']."-".$row3['name']."</option>";   
                                                        }
                                                    }
                                                    echo "</select></div>";
                                            }else{
                                                if($row['DATA_TYPE']!="enum"){
                                                    echo "<input type=\"text\" name=\"".$row['COLUMN_NAME']."\" maxlength=\"";
                                                    if($row['CHARACTER_MAXIMUM_LENGTH']=="" || $row['CHARACTER_MAXIMUM_LENGTH']=="0"){
                                                        echo "11";
                                                    }else{
                                                        echo $row['CHARACTER_MAXIMUM_LENGTH'];
                                                    }
                                                    echo "\" ";
                                                    if($row['EXTRA']!=""){
                                                        echo "placeholder=\"".$row['EXTRA']."\" ";
                                                    }else if($row['IS_NULLABLE']=="YES"){
                                                        echo "placeholder=\"NULL\" ";
                                                    }
                                                    if($row['IS_NULLABLE']!="YES" && $row['EXTRA']!="auto_increment"){
                                                        echo "required";
                                                    }
                                                    echo ">";
                                                }else{
                                                    echo "<div class=\"form-group\"><select class=\"form-control\" name=\"".$row['COLUMN_NAME']."\">";
                                                    $sql="SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING(COLUMN_TYPE, 7, LENGTH(COLUMN_TYPE) - 8), \"','\", 1 + units.i + tens.i * 10) , \"','\", -1)
                                                    FROM INFORMATION_SCHEMA.COLUMNS
                                                    CROSS JOIN (SELECT 0 AS i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) units
                                                    CROSS JOIN (SELECT 0 AS i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) tens
                                                    WHERE TABLE_NAME = '".$_GET['table']."' 
                                                    AND COLUMN_NAME = '".$row['COLUMN_NAME']."'";
                                                    $result = $con->query($sql);
                                                    if($result->num_rows>1){
                                                        while($row=$result->fetch_assoc()){
                                                            foreach($row as $value){
                                                                echo "<option value=\"$value\">$value</option>";
                                                            }
                                                        }
                                                    }
                                                    echo "</select></div>";
                                                }
                                            }
                                            if(isset($row['COLUMN_KEY']) && $row['COLUMN_KEY']!=''){
                                                if($row['COLUMN_KEY']=="PRI"){
                                                    echo "PK";
                                                }else if($row['COLUMN_KEY']=="UNI"){
                                                    echo "Unique";
                                                }else if($res2->num_rows>0){
                                                    echo "FK";
                                                }
                                            }
                                            echo "</td></tr>";
                                        }
                                    }
                                ?>
                            </table>
                            <button type="submit" class="btn btn-success" name="submit">Submit</button>
                        </form>
                    <?php else:?>
                        <h3 class="text-danger">Select Table</h3>                    
                    <?php endif?>
                <?php else:?>
                    <h3>Select Database</h3>
                <?php endif?>
            </div>
        </div>
        <div class="row ml-3">
            <?php
                if(isset($_SESSION['sql'])){
                    echo $_SESSION['sql'];
                    unset($_SESSION['sql']);
                }else{
                    if(isset($_GET) && isset($_GET['database'])){
                        if(!isset($_GET['table'])){
                            echo "<h3 class='text-danger'>Select Table</h3>";
                        }else{
                            echo "<h3>Submit Form</h3>";
                        }
                    }else{
                        echo "<h3>Select Database</h3>";
                    }
                }
            ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
            integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
            integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
            integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous">
    </script>
</body>
</html>
