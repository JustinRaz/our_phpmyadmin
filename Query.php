<?php
    session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Tables</title>
        <?php require "dependencies.php"?>

    <style>
        .fake-link {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
    </head>
    <body>
        <?php require "navbar.php"?>
        <script
                src="https://code.jquery.com/jquery-3.5.1.min.js"
                integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
                crossorigin="anonymous">
        </script>

<?php
    $conn = new mysqli('localhost', 'root', '', 'INFORMATION_SCHEMA');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $result = $conn->query( "SELECT TABLE_NAME AS from_table,
                                    COLUMN_NAME AS from_column,
                                    REFERENCED_TABLE_NAME AS to_table,
                                    REFERENCED_COLUMN_NAME AS to_column
                                    FROM KEY_COLUMN_USAGE
                                    WHERE TABLE_SCHEMA = '{$_GET['database']}' 
                                    AND REFERENCED_COLUMN_NAME IS NOT NULL");

    $_SESSION['FK'] = array();
    while ($row = $result->fetch_assoc()){
        if (!isset($_SESSION['FK'][$row['from_table']])){
            $_SESSION['FK'][$row['from_table']] = array();
        }
        $_SESSION['FK'][$row['from_table']][$row['from_column']] = array('to_table'=>$row['to_table'],'to_column'=>$row['to_column']);
    }
    $result = $conn->query( "SELECT TABLE_NAME AS `table`,
                                    COLUMN_NAME AS `column`, 
                                    DATA_TYPE AS datatype 
                                    FROM INFORMATION_SCHEMA.COLUMNS 
                                    WHERE TABLE_SCHEMA = '{$_GET['database']}'");
                                    echo "</br>";
    $_SESSION['col_definition'] = array();
    while ($row = $result->fetch_assoc()){
        if (!isset($_SESSION['col_definition'][$row['table']])){
            $_SESSION['col_definition'][$row['table']] = array();
        }
        $_SESSION['col_definition'][$row['table']][$row['column']] = $row['datatype'];
    }
?>
<script>
// Initializing js var FK from php session var $_SESSION['FK']
    var FK = {
<?php
    foreach ($_SESSION['FK'] as $key1=>$value1) : ?>
        <?php echo $key1 ?> : {
        <?php foreach ($value1 as $key2=>$value2) : ?>
            <?php echo $key2 ?> : {
                                    to_table : <?php echo "'".$value2['to_table']."'" ?>,
                                    to_column : <?php echo "'".$value2['to_column']."'" ?>,
                                },
        <?php endforeach; ?>
        },
<?php endforeach; ?>
            };
// Initializing js var col_definition from php session var $_SESSION['col_definition']
    var col_definition = {
<?php
    foreach ($_SESSION['col_definition'] as $key1=>$value1) : ?>
        <?php echo $key1 ?> : {
        <?php foreach ($value1 as $key2=>$value2) : ?>
            <?php echo $key2 ?> : '<?php echo $value2 ?>',
        <?php endforeach; ?>
        },
<?php endforeach; ?>
        };
</script>


<div class="container">
    <h4>Tables in <?php echo $_GET['database']?></h4> 
        
    <p> <em>select table</em> </p>
        <ul>
        <?php
            $result = $conn->query("SHOW TABLES FROM {$_GET['database']}");
            while ($row = $result->fetch_row()) : ?> 
                <li id="<?php echo $row[0] ?>" class="tables"><span class='fake-link'><?php echo $row[0] ?></span></li>
            <?php endwhile; ?>
            <?php if($result->num_rows == 0): ?>
                No tables found
            <?php endif?>
        </ul>
        <br/>

        <hr/><h4>Left Columns to Select Choices</h4><br/>
        <ul id="selectOptions">
        </ul>

        <hr/><h4>Columns to Join Choices</h4><br/>
        <ul id="columnOptions">
        </ul>

        <div class="row">

        <script>
            var fromUrl = '';
            var selectUrl = '';
            var whereUrl = '';
            var joinUrl = '';
            var whereCount = 0;
            var joinCount = 0;
            var possibleJoins = {};
            var from_table;
            var choiceObject = {};
            var tableJoinRecord = {};
            
            function SelectWhereChoices(appenedAt,table){
                $(appenedAt).append(
                    "<button class ='btn btn-primary' id='"+table+"Check' style='margin-right:3px'>Check all</button>"
                );
                $(appenedAt).append(
                    "<button class ='btn btn-secondary' id='"+table+"Uncheck'>Uncheck all</button>"
                );
                var getDatatype = '';
                for (var columns in col_definition[table]){
                    getDatatype = checkDatatype(col_definition[table][columns].toLowerCase());
                    $(appenedAt).append(
                        $('<div/>',{
                            'class':'div'+table
                        }).append(
                            $('<input/>',{
                                'type':'checkbox',
                                'class':'form-check-input selectColumns'+table,
                                'id':'selectColumns'+table+columns,
                                'value':columns,
                                'checked':'checked',
                            })
                        ).append(
                            $('<label/>',{
                    
                                'class': 'form-check-label',
                                'for':'selectColumns'+table+columns,
                                text:columns,
                            })
                        ).append(
                            $('<span/>',{
                                'class': 'form-check'
                            }).addClass('divWhere').addClass('div'+table+columns).append(
                                $('<span/>',{
                                    'style': 'font-weight: 700; margin-left: 10px',
                                    text:'where: '
                                })
                            ).append(
                                $('<label/>',{
                                    'for': 'where'+table+columns,
                                })
                            ).append(
                                $('<select/>',{
                                    'id': 'where'+table+columns,
                                }).addClass('where'+table).addClass(getDatatype)
                            )
                        )
                    )
                    
                    
                }
                $('.where'+table).each(function(){
                    if($(this).hasClass('int') || $(this).hasClass('string')){
                        $(this).append(
                            $('<option/>',{
                                'value':'',
                                text:'None',
                            })
                        ).append(
                            $('<option/>',{
                                'value':'IS NULL',
                                text:'IS NULL',
                            })
                        ).append(
                            $('<option/>',{
                                'value':'IS NOT NULL',
                                text:'IS NOT NULL',
                            })
                        ).append(
                            $('<option/>',{
                                'value':'=',
                                text: '=',
                            })
                        ).append(
                            $('<option/>',{
                                'value':'!=',
                                text: '!=',
                            })
                        ).append(
                            $('<option/>',{
                                'value':'<',
                                text: '<',
                            })
                        ).append(
                            $('<option/>',{
                                'value':'<=',
                                text: '<=',
                            })
                        ).append(
                            $('<option/>',{
                                'value':'>',
                                text: '>',
                            })
                        ).append(
                            $('<option/>',{
                                'value':'>=',
                                text: '>=',
                            })
                        )
                    }else {
                        $(this).append(
                            $('<option/>',{
                                'value':'',
                                text:'N/A (No Choices)',
                            })
                        )
                    }
                });
                $('.where'+table).on('change',function(){
                    var columnForTextBox = $(this).parent().parent().children("input[type='checkbox']").val();
                    if (['=','!=','<','>','<=','>='].includes($(':selected',this).val())){
                        if ($(this).parent().children('#whereInput'+table+columnForTextBox).length==0){
                            if ($(this).hasClass('int')){
                                $(this).parent().append(
                                    $("<input/>",{
                                        'type': 'number',
                                        'class':'whereInput'+table,
                                        'id':'whereInput'+table+columnForTextBox,
                                        'placeholder': 'Filter Number Input Here'
                                    })
                                ).append(
                                    $("<label/>",{
                                        'for':'whereInput'+table+columnForTextBox,
                                    })
                                );
                            }else if ($(this).hasClass('string')){
                                var columnForTextBox = $(this).parent().parent().children("input[type='checkbox']").val();
                                $(this).parent().append(
                                    $("<input/>",{
                                        'type': 'text',
                                        'class':'whereInput'+table,
                                        'id':'whereInput'+table+columnForTextBox,
                                        'placeholder': 'Filter Text Input Here'
                                    })
                                ).append(
                                    $("<label/>",{
                                        'for':'whereInput'+table+columnForTextBox,
                                    })
                                );
                            }
                        }
                    }else {
                        console.log($(this).parent().children('#whereInput'+table+columnForTextBox));
                        $(this).parent().children('#whereInput'+table+columnForTextBox).remove();
                        $(this).parent().children("label[for='whereInput"+table+columnForTextBox+"']").remove();
                    }
                });
                $('#'+table+'Uncheck').on('click', function(){
                    $('.selectColumns'+table).prop('checked', false);
                });
                $('#'+table+'Check').on('click', function(){
                    $('.selectColumns'+table).prop('checked', true);
                });
            }
            function checkDatatype(varName){
                if (checkIfInt(varName)){
                    return 'int';
                }else if (checkIfString(varName)){
                    return 'string';
                }else {
                    return 'else';
                }
            }
            function checkIfInt(varName){
                return (varName.includes('int') && varName!='point' && varName!='multipoint') ||
                        (['decimal','float','double'].includes(varName));
            }
            function checkIfString(varName){
                return varName.includes('char') || varName.includes('text') || varName=='enum';
            }
            function InitialValuesForFrom(){
                var selectExists = false;

                fromUrl += 'from[]='+from_table+'&';
                selectUrl += 'select[0][]=';
                $('.selectColumns'+from_table).each(function(){
                    if ($(this).prop('checked')){
                        selectUrl += $(this).val()+'&select[0][]=';
                        selectExists = true;
                    }
                });
                if (selectExists){
                    selectUrl = selectUrl.substring(0,selectUrl.length-'&select[0][]='.length);
                    selectExists = false;
                }
                $('.div'+from_table).each(function(){
                    if ($(this).children('.divWhere').children('select').val()!='' && $(this).children('.divWhere').children('.whereInput'+from_table).val()!=''){
                        whereUrl += '&where['+Number(whereCount)+'][]='+from_table;
                        whereUrl += '&where['+Number(whereCount)+'][]='+$(this).children('.selectColumns'+from_table).val();
                        if ($(this).children('.divWhere').children('select').val().includes('NULL')){
                            if ($(this).children('.divWhere').children('select').val().includes('NOT')){
                                whereUrl += '&where['+Number(whereCount)+'][]=IS NOT';
                            }else {
                                whereUrl += '&where['+Number(whereCount)+'][]=IS';
                            }
                            whereUrl += '&where['+Number(whereCount)+'][]=NULL'
                        }else {
                            whereUrl += '&where['+Number(whereCount)+'][]='+$(this).children('.divWhere').children('select').val();
                            if ($(this).children('.divWhere').children('select').hasClass('string')){
                                whereUrl += "&where["+Number(whereCount)+"][]='"+$(this).children('.divWhere').children('.whereInput'+from_table).val()+"'";
                            }else {
                                whereUrl += "&where["+Number(whereCount)+"][]="+$(this).children('.divWhere').children('.whereInput'+from_table).val();
                            }
                        }
                        whereUrl += '&where['+Number(whereCount)+'][]=0';
                        whereCount++;
                    }
                });
            }
            $(document).ready(function(){
                $(".tables").on("click",function(){
                    from_table = $(this).attr('id'); //get clicked table
                    // Select Section
                    $("#selectOptions").empty();
                    if (joinCount == 0){
                        // $("#selectOptions").append("a");
                        SelectWhereChoices("#selectOptions",from_table);
                        
                    }
                    // Join Section
                    var firstIter = true;
                    if (joinCount == 0){
                        choiceObject = FK;
                    }else {
                        choiceObject = possibleJoins;
                    }
                    $("#columnOptions").empty();
                    // $("#columnOptions").append("<hr/>Columns to Join Choices:<br/>")
                    for (var columns in choiceObject[from_table]){
                        $("#columnOptions").append(
                            "<li class='joinColumns' id='joinColumns"+columns+"'>"+columns
                                +" (<span class='joinKind LJ'>Left Join</span>,"
                                +" <span class='joinKind IJ'>Inner Join</span>,"
                                +" <span class='joinKind RJ'>Right Join</span>,) "
                                + "<i>with Table</i>: " + choiceObject[from_table][columns].to_table
                                +" <i> at Column: </i>"+choiceObject[from_table][columns].to_column
                            +"</li>"
                        );
                        $("#columnOptions").append("Right Columns to Join Choices:<br/>");
                        SelectWhereChoices("#columnOptions",choiceObject[from_table][columns].to_table); 
                    }
                    // After Choosing Join Option
                    // console.log(from_table);
                    if (joinCount == 0){
                        $("#groupby, #orderby").empty();
                        $("#groupby, #orderby").append("<option value=''>None</option>");
                        $("<optgroup label='"+from_table+"'>").appendTo('#groupby, #orderby');
                        for (var columns in col_definition[from_table]){
                            $("optgroup[label='"+from_table+"']",'#groupby, #orderby').append(
                                "<option value='"+columns+"'>"+columns+"</option>"
                            );
                        }
                    }
                });
                $('#columnOptions').on('click', '.joinKind', function() {
                    
                    var joinKind = Array.from(this.classList).find(eachClass => ['LJ','IJ','RJ'].includes(eachClass));
                    
                    var from_column = $(this).parent().attr("id").substring(11 /* 'joinColumns'.length */);

                    if (joinCount == 0){
                        InitialValuesForFrom();
                    }
                    $("<optgroup label='"+choiceObject[from_table][from_column].to_table+"'>").appendTo('#groupby, #orderby');
                    for (var columns in col_definition[choiceObject[from_table][from_column].to_table]){
                        $('#groupby>optgroup:last-child, #orderby>optgroup:last-child').append(
                            "<option value='"+columns+"'>"+columns+"</option>"
                        );
                    }
                    fromUrl += 'from[]='+choiceObject[from_table][from_column].to_table+'&';
                    selectUrl += '&select['+(Number(joinCount)+1)+'][]=';
                    $('.selectColumns'+choiceObject[from_table][from_column].to_table).each(function(){
                        if ($(this).prop('checked')){
                            selectUrl += $(this).val()+'&select['+(Number(joinCount)+1)+'][]=';
                            selectExists = true;
                        }
                    });
                    if (selectExists){
                        selectUrl = selectUrl.substring(0,selectUrl.length-('&select['+(Number(joinCount)+1)+'][]=').length);
                        selectExists = false;
                    }
                    $('.div'+choiceObject[from_table][from_column].to_table).each(function(){
                        if ($(this).children('.divWhere').children('select').val()!='' && $(this).children('.divWhere').children('.whereInput'+choiceObject[from_table][from_column].to_table).val()!=''){
                            whereUrl += '&where['+Number(whereCount)+'][]='+choiceObject[from_table][from_column].to_table
                            whereUrl += '&where['+Number(whereCount)+'][]='+$(this).children('.selectColumns'+choiceObject[from_table][from_column].to_table).val();
                            if ($(this).children('.divWhere').children('select').val().includes('NULL')){
                                if ($(this).children('.divWhere').children('select').val().includes('NOT')){
                                    whereUrl += '&where['+Number(whereCount)+'][]=IS NOT';
                                }else {
                                    whereUrl += '&where['+Number(whereCount)+'][]=IS';
                                }
                                whereUrl += '&where['+Number(whereCount)+'][]=NULL'
                            }else {
                                whereUrl += '&where['+Number(whereCount)+'][]='+$(this).children('.divWhere').children('select').val();
                                
                                if ($(this).children('.divWhere').children('select').hasClass('string')){
                                    whereUrl += "&where["+Number(whereCount)+"][]='"+$(this).children('.divWhere').children('.whereInput'+choiceObject[from_table][from_column].to_table).val()+"'";
                                }else {
                                    whereUrl += '&where['+Number(whereCount)+'][]='+$(this).children('.divWhere').children('.whereInput'+choiceObject[from_table][from_column].to_table).val();
                                }
                            }
                            whereUrl += '&where['+Number(whereCount)+'][]='+(Number(joinCount)+1);
                            whereCount++;
                        }
                    });
                    joinUrl += 'join['+joinCount+'][]='+joinKind+'&';
                    joinUrl += 'join['+joinCount+'][]='+from_table+'&';
                    joinUrl += 'join['+joinCount+'][]='+from_column+'&';
                    if (joinCount == 0){
                        joinUrl += 'join[0][]=0&';
                        tableJoinRecord[from_table] = 0;
                    }else {
                        joinUrl += 'join['+joinCount+'][]='+tableJoinRecord[from_table]+'&';
                    }
                    joinUrl += 'join['+joinCount+'][]='+choiceObject[from_table][from_column].to_table+'&';
                    joinUrl += 'join['+joinCount+'][]='+choiceObject[from_table][from_column].to_column+'&';
                    joinUrl += 'join['+joinCount+'][]='+(Number(joinCount)+1)+'&';
                    if (!(choiceObject[from_table][from_column].to_table in tableJoinRecord)){
                        tableJoinRecord[choiceObject[from_table][from_column].to_table] = Number(joinCount)+1;
                    }
                    $("#columnOptions").empty();
                    $("#selectOptions").empty();
                    if (joinCount == 0){
                        possibleJoins[from_table] = {};
                        $.extend(possibleJoins[from_table], FK[from_table]);
                    }
                    delete possibleJoins[from_table][from_column];
                    directedToTable = FK[from_table][from_column].to_table;
                    if ( !(directedToTable in possibleJoins) && (directedToTable in FK) ){
                        possibleJoins[directedToTable] = {};
                        $.extend(possibleJoins[directedToTable], FK[directedToTable]);
                    }
                    joinCount++;
                });
                $("#reset").on("click", function(){
                    fromUrl = '';
                    selectUrl = '';
                    whereUrl = '';
                    joinUrl = '';
                    joinCount = 0;
                    whereCount = 0;
                    possibleJoins = {};
                    tableJoinRecord = {};

                    $("#selectOptions, #columnOptions, #groupby, #orderby").empty();
                    $("#groupby, #orderby").append(
                        "<option>None</option>"
                    )
                });
                $("#submit, #submitWithCount").on("click", function(){
                    whereUrl = whereUrl.substring(1);
                    if (from_table != ''){
                        if (jQuery.isEmptyObject(possibleJoins)){
                            InitialValuesForFrom();
                        }
                        var oldUrl = window.location.href;
                        var withoutQuery = oldUrl.substring(0,oldUrl.lastIndexOf('/'));
                        var databaseUrl = oldUrl.substring(oldUrl.indexOf('database'));
                        var nextQuery = databaseUrl.indexOf('&');
                        if (nextQuery == -1){ // Database query is the last
                            nextQuery = databaseUrl.length;
                        }
                        databaseUrl = databaseUrl.substring(databaseUrl.indexOf('=')+1, nextQuery);

                        var url = withoutQuery+'/Select.php?database='+databaseUrl+'&';
                        
                        var groupbyUrl = '';
                        if ($('#groupby').val() !== ''){
                            groupbyUrl += '&group[]='+$(':selected','#groupby').parent().attr('label');
                            groupbyUrl += '&group[]='+$('#groupby').val();
                            groupbyUrl += '&group[]='+($(':selected','#groupby').parent().index()-1);
                        }
                        var orderbyUrl = '';
                        if ($('#orderby').val() !== ''){
                            orderbyUrl += '&order[]='+$(':selected','#orderby').parent().attr('label');
                            orderbyUrl += '&order[]='+$('#orderby').val();
                            orderbyUrl += '&order[]='+($(':selected','#orderby').parent().index()-1);
                        }
                        if (jQuery.isEmptyObject(possibleJoins)){
                            if (whereUrl !== ''){
                                url += fromUrl+selectUrl+'&'+whereUrl+groupbyUrl+orderbyUrl;
                            }else {
                                url += fromUrl+selectUrl+groupbyUrl+orderbyUrl;
                            }
                        }else {
                            if (whereUrl !== ''){
                                url += fromUrl+selectUrl+'&'+whereUrl+'&'+joinUrl.substring(0,joinUrl.length-1)+groupbyUrl+orderbyUrl;
                            }else {
                            url += fromUrl+selectUrl+'&'+joinUrl.substring(0,joinUrl.length-1)+groupbyUrl+orderbyUrl;
                            }
                        }
                        if ($(this).attr('id')=='submitWithCount'){
                            url += '&count=1';
                        }
                        window.location.href = url+'&reroute=./JoinResult.php';
                    }
                });
            });
        </script>

        </div>
        
        <div>
            <hr/>
            <h4>Group and Order</h4>
            <label for="groupby">Group By Options:</label>
            <select id="groupby">
                <option value=''>None</option>
            </select>
            <br/>
            <label for="orderby">Order By Options:</label>
            <select id="orderby">
                <option value=''>None</option>
            </select>
            
        </div>
        <div>
        
        <hr/>
            <h4>Perform Query</h4>
        <p>
                <button class='btn btn-danger' id="reset">RESET</button>
                <button class='btn btn-primary' id="submit">Submit</button>
                <button class='btn btn-primary' id="submitWithCount">Submit with Count</button>
            </p>
        </div>
</div>
        
    </body>
</html>
