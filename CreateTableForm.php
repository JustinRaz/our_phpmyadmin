<?php
    session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Databases</title>    
    <?php require "dependencies.php"?>
</head>
<body>
    <a href="./Tables.php?database=<?php echo $_GET['database'] ?>">Back</a>
    <form action="./CreateTable.php" method="POST">
        <label>
            Table Name
            <input type="text" id="tableName" required="required"/>
        </label>
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
                <label>
                    Primary Key + Auto Increment
                    <input type="checkbox" class="ai" value="ai"/>
                </label>
                <input type="button" class="delete" value="Delete">
            </div>
        </div>
        <input type="button" class="add" value="Add another column"/>
        <input type="submit" class="submit" value="Create Table"/>
        <input type="hidden" name="query" id="query"/>
        <input type="hidden" name="database" value="<?php echo $_GET['database'] ?>"/>
    </form>
    <script>
        let message = '';
        let database = '<?php echo $_GET['database'] ?>';
        $(document).ready(function(){
            $(".add").on("click",function(){
                AddColumn();
            });
            $("#createTableForm").on("click",".delete",function(){
                DeleteColumn($(this).parent());
            });
            $("#createTableForm").on("change",".type",function(){
                RefreshValues($(this).parent().parent(),$(this).find(":selected").val());
            });
            $("form").on("submit",function(e){
                let success = false;
                if ($("#query").val()==''){
                    e.preventDefault();
                    switch (success) {
                        case CheckIfEverythingIsFilled() : break;
                        case CheckForInvalidValues() : break;
                        default : {
                            success = true;
                        }
                    }
                    if (success){
                        $("#query").val(CompileInput());
                        $("form").submit();
                    }else {
                        alert(message);
                    }
                }

            });
        });
        function AddColumn(){
            let $clonedColumn = $("#firstColumn").clone();
            $clonedColumn.find("input[type='text']").val("");
            $clonedColumn.find("input[type='number']").val("");
            $clonedColumn.find("select").val("int");
            $clonedColumn.find("input[type='checkbox']").prop('checked',false);
            $($clonedColumn).appendTo('#createTableForm');
        }
        function DeleteColumn($column){
            if ($column.parent().children().length > 1){ // Check if only child
                $column.remove();
            }
        }
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
            if (type != 'int'){
                $column.find(".ai").prop('checked',false);
            }
        }
        function CheckIfEverythingIsFilled(){
            let success = true;
            let $columns = $("#createTableForm").children();
            if ($("#tableName").val() == ''){
                success = false;
                message = 'Table Name must be specified';
            }else {
                for (let i=0 ; i<$columns.length ; i++){
                    let $column = $($columns[i]);
                    if ($column.find('.name').val() == ''){
                        success = false;
                        message = 'An empty name field exists';
                    }else if ($column.find('.type').find(":selected").val()=='varchar' && $column.find('.length').val()==''){
                        success = false;
                        message = 'Columns with type varchar must have a specified length';
                    }
                }
            }
            return success;
        }
        function CheckForInvalidValues(){
            let success = false;
            switch (success) {
                case CheckForRepeatedNames() : {
                    message = 'Columns with identical names exist';
                    break;
                }
                case ValidateLength() : {
                    break;
                }
                case ValidateDefaultValues() : {
                    break;
                }
                case ValidateAutoIncrements() : {
                    message = 'Either multiple columns with auto increment exists or columns with non-integer type and auto incremented exists';
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
            let $names = $("#createTableForm").find('.name');

            for (let i=0 ; i<$names.length && success==true ; i++){
                for (let j=i+1 ; j<$names.length && success==true ; j++){
                    if ($($names[i]).val() == $($names[j]).val()){
                        success = false;
                    }
                }
            }
            return success;
        }
        function ValidateLength(){
            let success = true;
            let $columns = $("#createTableForm").children();

            for (let i=0 ; i<$columns.length ; i++){
                let $column = $($columns[i]);
                let type = $column.find('.type').find(":selected").val();
                let length = $column.find(".length").val();
                if (type=='datetime' && length>6){
                    message = 'Maximum precision for date/datetime types is 6';
                    success = false;
                }else if (type=='date' && length!=''){
                    message = 'Column with date type is specified with a length';
                    success = false;
                }
            }
            return success;
        }
        function ValidateDefaultValues(){
            let success = true;
            let $columns = $("#createTableForm").children();

            for (let i=0 ; i<$columns.length ; i++){
                let $column = $($columns[i]);
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
                    }else if ($column.find('.ai').prop('checked')==true){
                        message = 'Column with auto_increment is given a non-empty default value';
                        success = false;
                    }
                }
            }
            return success;
        }
        function ValidateAutoIncrements(){
            let success = true;
            let aiCount = 0;
            let $columns = $("#createTableForm").children();

            for (let i=0 ; i<$columns.length ; i++){
                let $column = $($columns[i]);
                let ai = $column.find(".ai").val();
                if ($column.find('.ai').prop('checked')==true){
                    aiCount++;
                    if ($column.find('.type').find(":selected").val()!='int'){
                        success = false;
                    }
                }
            }
            if (aiCount > 1){
                success = false;
            }
            return success;
        }
        function CompileInput(){
            let names = [];
            let types = [];
            let lengths = [];
            let defaults = [];
            let nulls = [];
            let ais = [];
            let tableName = $("#tableName").val();
            let $columns = $("#createTableForm").children();
            
            let query = 'CREATE TABLE '+database+'.`'+tableName+'`('

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
                ais.push($column.find('.ai').prop('checked'));
            }
            for (let i=0 ; i<names.length ; i++){
                query += names[i] + ' ';
                query += types[i];
                if (lengths[i] != ''){
                    query += '('+ lengths[i] + ') ';
                }else {
                    query += ' ';
                }
                if (defaults[i] != ''){
                    query += 'DEFAULT '+ defaults[i] + ' ';
                }
                if (nulls[i] == false){
                    query += 'NOT NULL ';
                }
                if (ais[i] == true){
                    query += 'PRIMARY KEY AUTO_INCREMENT';
                }
                if (i+1 < $columns.length){
                    query += ',';
                }
            }
            query += ')';
            return query;
        }
    </script>
</body>
</html>