<?php
    session_start();

    $conn = new mysqli('localhost','root','');
    if (isset($_POST['query'])){
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // die($_POST['query']);
        if ($conn->query($_POST['query']) == false){
            die($conn->error);
        }
        if (isset($_POST['rename'])){
            header("Location: ./UpdateTableForm.php?database={$_GET['database']}&table={$_POST['rename']}");
        }
    }
    $conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Databases</title>    
    <?php require "dependencies.php"?>
</head>
<body>
    <a href="./Tables.php?database=<?php echo $_GET['database'] ?>">Back</a>
    <form id="renametableform" action="./UpdateTableForm.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $_GET['table'] ?>" method="POST">
        <label for="tablename">Rename Table</label>
            <input id="tablename" type="text" name="query" value="<?php echo $_GET['table']?>"/>
        <input class="renametable" type="button" value="Rename"/>
        <input type="hidden" name="query" id="renametablequery"/>
        <input type="hidden" name="rename" id="newroutetable"/>
    </form>
    <form id="addform" action="./UpdateTableForm.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $_GET['table'] ?>" method="POST">
        <div id="createTableForm">
            <div id="firstColumn" class="column">
                <label>
                    Name
                    <input type="text" class="name" required="required"/>
                </label>
                <label>
                    Type
                    <select class="type"  required="required">
                        <option value="int" selected="selected">Int</option>
                        <option value="varchar">Varchar</option>
                        <option value="text">Text</option>
                        <option value="date">Date</option>
                        <option value="datetime">DateTime</option>
                    </select>
                </label>
                <label>
                    Length
                    <input type="number" class="length"/>
                </label>
                <label>
                    Default Value
                    <input type="text" class="default"/>
                </label>
                <label>
                    Nullable
                    <input type="checkbox" class="null" value="null"/>
                </label>
                <input type="submit" class="submit" value="Add Column to this Table"/>
            </div>
        </div>
        <input type="hidden" name="query" id="addquery"/>
        <input type="hidden" name="database" value="<?php echo $_GET['database'] ?>"/>
    </form>
    <form id="updateform" action="./UpdateTableForm.php?database=<?php echo $_GET['database'] ?>&table=<?php echo $_GET['table'] ?>" method="POST">
        <ol class="columns"> Columns:
            <?php 
                $conn = new mysqli('localhost','root','');
                $result = $conn->query( "SELECT COLUMN_NAME AS `column`,
                                                DATA_TYPE AS datatype,
                                                CHARACTER_MAXIMUM_LENGTH AS `length`,
                                                COLUMN_DEFAULT AS `default`,
                                                IS_NULLABLE AS nullable,
                                                EXTRA AS `extra`
                                            FROM INFORMATION_SCHEMA.COLUMNS 
                                            WHERE TABLE_SCHEMA = '{$_GET['database']}'
                                                AND TABLE_NAME = '{$_GET['table']}'");
                while($row = $result->fetch_assoc()) : ?>
                <li id="<?php echo $row['column'] ?>">
                    <span class="name"><?php echo $row['column'] ?></span>
                    <input type="text" class="newname" placeholder="rename..."/>
                    <input type="button" class="rename" value="Rename"/>
                    <input type="button" class="remove" value="Remove Column"/>
                    <input type="hidden" class="datatype" value="<?php echo $row['datatype'] ?>"/>
                    <input type="hidden" class="length" value="<?php echo $row['length'] ?>"/>
                    <input type="hidden" class="default" value="<?php echo $row['default'] ?>"/>
                    <input type="hidden" class="nullable" value="<?php echo $row['nullable'] ?>"/>
                    <input type="hidden" class="extra" value="<?php echo $row['extra'] ?>"/>
                </li>
            <?php endwhile; ?>
        </ol>
        <input type="hidden" name="query" id="updatequery"/>
    </form>
    <script>
        let message = '';
        let database = '<?php echo $_GET['database'] ?>';
        let table = '<?php echo $_GET['table'] ?>';
        $(document).ready(function(e){
            $("#createTableForm").on("change",".type",function(){
                RefreshValues($(this).parent().parent(),$(this).find(":selected").val());
            });
            $(".renametable").on("click",function(){
                let newName = $("#tablename").val();
                if (table.toLowerCase() == newName.toLowerCase()){
                    alert('Inputted name is the same as the current table name');
                }else {
                    $("#renametablequery").val('RENAME TABLE ' +database+ '.`' +table+ '` TO ' +database+ '.`' +newName+ '`;');
                    $("#newroutetable").val(newName);
                    $("#renametableform").submit();
                }
                console.log('RENAME TABLE ' +database+ '.`' +table+ '` TO `' +newName+ '`;');
            });
            $("#addform").on("submit",function(e){
                let success = false;
                if ($("#addquery").val()==''){
                    e.preventDefault();
                    switch (success) {
                        case CheckIfEverythingIsFilled() : break;
                        case CheckForInvalidValues() : break;
                        default : {
                            success = true;
                        }
                    }
                    if (success){
                        $("#addquery").val(CompileInput());
                        $("#addform").submit();
                    }else {
                        alert(message);
                    }
                }
            });
            $(".rename").on("click",function(){
                let $column = $(this).parent();
                if ($column.find('.newname').val() == ''){
                    alert('Input is empty');
                }else if ($column.find('.rename').val() == $column.find('.newname').val()){
                    alert('Inputted name is the same as the current column name');
                }else if (CheckDuplicates($column.find('.newname').val())){
                    alert('Another column already has a similar name.');
                }else {
                    $("#updatequery").val(CompileRename($column));
                    $("#updateform").submit();
                }
            });
            $(".remove").on("click",function(){
                $("#updatequery").val(Remove($(this).parent()));
                console.log(Remove($(this).parent()));
                $("#updateform").submit();
            });
        });
        function RefreshValues($column,type){
            let defaultVal = $column.find('.default').val();
            let refreshDefaultVal = false;
            if (type!='varchar' && type!='text'){
                refreshDefaultVal = true;
                // If new type is int and default input is not integer
                if (type=='int' && $.isNumeric(defaultVal) && Math.floor(defaultVal)==defaultVal){
                    refreshDefaultVal = false;
                }else if (type=='datetime' && defaultVal.toLowerCase()=='current_timestamp'){
                    refreshDefaultVal = false;
                }
            }
            if (refreshDefaultVal){
                $column.find('.default').val('');
            }
        }
        function CheckIfEverythingIsFilled(){
            let success = true;
            let $columns = $("#createTableForm").children();
            let $column = $($columns[0]);
            if ($column.find('.name').val() == ''){
                success = false;
                message = 'An empty name field exists';
            }else if ($column.find('.type').find(":selected").val()=='varchar' && $column.find('.length').val()==''){
                success = false;
                message = 'Columns with type varchar must have a specified length';
            }
            return success;
        }
        function CheckForInvalidValues(){
            let success = false;
            switch (success) {
                case CheckForRepeatedNames() : {
                    message = 'A column already has the same name';
                    break;
                }
                case ValidateLength() : {
                    break;
                }
                case ValidateDefaultValues() : {
                    break;
                }
                default : {
                    success = true;
                }
            }
            return success;
        }
        function CheckForRepeatedNames(){
            let success = true;
            let name = $("#createTableForm").find('.name').val();
            let $othernames = $(".columns").find('.name');

            for (let i=0 ; i<$othernames.length && success==true ; i++){
                if ($othernames.eq(i).html() == name){
                    success = false;
                }
            }
            return success;
        }
        function ValidateLength(){
            let success = true;
            let $columns = $("#createTableForm").children();
            let $column = $($columns[0]);
            let type = $column.find('.type').find(":selected").val();
            let length = $column.find(".length").val();

            if (type=='datetime' && length>6){
                message = 'Maximum precision for date/datetime types is 6';
                success = false;
            }else if (type=='date' && length!=''){
                message = 'Column with date type is specified with a length';
                success = false;
            }
            return success;
        }
        function ValidateDefaultValues(){
            let success = true;
            let $columns = $("#createTableForm").children();
            let $column = $($columns[0]);
            let defaultVal = $column.find(".default").val();

            if (defaultVal != ''){
                if ($column.find('.type').find(":selected").val()=='int' && !($.isNumeric(defaultVal) && Math.floor(defaultVal)==defaultVal)){
                    message = 'Columns with invalid default values relative to column type exists'
                    success = false;
                }else if ($column.find('.type').find(":selected").val()=='datetime'){
                    if (defaultVal.toLowerCase()!='current_timestamp'){
                        message = 'Datetime type in this application is limited to "current_timestamp';
                        success = false;
                    }
                }else if($column.find('.type').find(":selected").val()=='date'){
                    message = 'No supported default values for date type in this application';
                    success = false;
                }
            }
            return success;
        }
        function CompileInput(){
            let names = [];
            let types = [];
            let lengths = [];
            let defaults = [];
            let nulls = [];
            let $columns = $("#createTableForm").children();
            
            let query = 'ALTER TABLE '+database+'.`'+table+'` ADD '

            for (let i=0 ; i<$columns.length ; i++){
                let $column = $($columns[i]);
                names.push('`'+$column.find('.name').val()+'`');
                types.push($column.find('.type').find(":selected").val());
                lengths.push($column.find('.length').val());
                if ($column.find('.default').val().toLowerCase() == 'current_timestamp'){
                    defaults.push('CURRENT_TIMESTAMP'); // Not including quotations
                }else if ($column.find('.default').val()==''){
                    defaults.push('');
                }else {
                    defaults.push("'"+$column.find('.default').val()+"'");
                }
                nulls.push($column.find('.null').prop('checked'));
            }
            query += names[0] + ' ';
            query += types[0];
            if (lengths[0] != ''){
                query += '('+ lengths[0] + ') ';
            }else {
                query += ' ';
            }
            if (defaults[0] != ''){
                query += 'DEFAULT '+ defaults[0] + ' ';
            }
            if (nulls[0] == false){
                query += 'NOT NULL ';
            }
            console.log(query);
            return query;
        }
        function CheckDuplicates(newname){
            let $columns = $(".columns").find('.name');

            for (var i=0 ; i<$columns.length && newname!=$columns.eq(i).html() ; i++){}
            return i<$columns.length ? true : false;
        }
        function CompileRename($column){
            let query = 'ALTER TABLE ' +database+ '.`' +table+ '` CHANGE `' +$column.find('.name').html()+ '` `'
                        +$column.find('.newname').val()+ '` ' +$column.find('.datatype').val();
            if ($column.find('.length').val()!='' && $column.find('.datatype').val()!='tinytext'){
                query += `(${$column.find('.length').val()}) `;
            }
            query += ' ';
            if ($column.find('.default').val() != ''){
                query += `DEFAULT ${$column.find('.default').val()} `;
            }
            if ($column.find('.nullable').val().toLowerCase() == 'yes'){
                query += `NULL `;
            }
            if ($column.find('.extra').val().toLowerCase().includes('auto_increment')){
                query += `AUTO_INCREMENT`;
            }
            console.log(query);
            return query;
        }
        function Remove($column){
            return 'ALTER TABLE ' +database+ '.`' +table+ '` DROP COLUMN ' +$column.find('.name').html();
        }
    </script>
</body>
</html>