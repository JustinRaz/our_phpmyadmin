<?php
    session_start();
?>
<!DOCTYPE html>
<html>
<head><title>Tables</title></head>
<body>
        <script
                src="https://code.jquery.com/jquery-3.5.1.min.js"
                integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
                crossorigin="anonymous">
        </script>
<?php
    $conn = new mysqli('localhost','root','',"{$_GET['database']}");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $rows = $conn->query("SELECT * FROM {$_GET['database']}.{$_GET['table']}");
    print_r($conn->error);
    $columns = $conn->query("SELECT COLUMN_NAME AS `column`
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = '{$_GET['table']}'
                                AND TABLE_SCHEMA = '{$_GET['database']}'");
    $columnContainer = array();
    while ($row = $columns->fetch_assoc()){
        $columnContainer[] = $row['column'];
    }
    $PKkey = '';
    $PKarray = array();
    $PKs = $conn->query("SHOW KEYS FROM {$_GET['database']}.{$_GET['table']} WHERE Key_name = 'PRIMARY'");
    while ($eachPK = $PKs->fetch_assoc()){
        $PKkey .= "&idKey[]={$eachPK['Column_name']}";
        $PKarray[] = $eachPK['Column_name'];
    }
    $result = $conn->query( "SELECT TABLE_NAME AS `table`,
                                    COLUMN_NAME AS `column`, 
                                    DATA_TYPE AS datatype 
                                    FROM INFORMATION_SCHEMA.COLUMNS 
                                    WHERE TABLE_NAME = '{$_GET['table']}'
                                    AND TABLE_SCHEMA = '{$_GET['database']}'");
    $_SESSION['table_col_definition'] = array();
    while ($row = $result->fetch_assoc()){
        if (!isset($_SESSION['table_col_definition'][$row['table']])){
            $_SESSION['table_col_definition'][$row['table']] = array();
        }
        $_SESSION['table_col_definition'][$row['table']][$row['column']] = $row['datatype'];
    }
?>
    <script>
        var col_definition = {
    <?php
        foreach ($_SESSION['table_col_definition'] as $key1=>$value1) : ?>
            <?php echo $key1 ?> : {
            <?php foreach ($value1 as $key2=>$value2) : ?>
                <?php echo $key2 ?> : '<?php echo $value2 ?>',
            <?php endforeach; ?>
            }
    <?php endforeach; ?>
            };
    </script>

    Rows in table <?php echo $_GET['database'] ?>.<?php echo $_GET['table'] ?>
    <table>
    <?php while ($row = $rows->fetch_assoc()) : ?>
        <tr>
        <?php foreach ($columnContainer as $value) : ?>
            <td>
                <?php echo $row[$value] ?>
            </td>
        <?php endforeach; ?>
        <?php
            $PKvalue = '';
            foreach ($PKarray as $value){
                $PKvalue .= "&idValue[]={$row[$value]}";
            }
        ?>
            <td>
                <?php if (!empty($PKarray)) : ?>
                    <a href='./Delete.php?database=<?php echo $_GET['database'] ?>&from=<?php echo $_GET['table'] ?><?php echo $PKkey ?><?php echo $PKvalue ?>'>DELETE</a>
                <?php else: ?>
                    <span>Cannot Delete, No Column With A Primary Key Constraint</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </table>
        <div id="deleteWhere">
        </div>
        <button id="submit">Delete</button>
    <script>
        var whereUrl = '';
        var whereCount = 0;
        function SelectWhereChoices(appenedAt,table){
            console.log(table);
            var getDatatype = '';
            for (var columns in col_definition[table]){
                getDatatype = checkDatatype(col_definition[table][columns].toLowerCase());
                $(appenedAt).append(
                    $('<div/>',{
                        'class':'div'+table
                    }).append(
                        $('<input/>',{
                            'type':'hidden',
                            'class':'selectColumns'+table,
                            'id':'selectColumns'+table+columns,
                            'value':columns,
                            'checked':'checked',
                        })
                    ).append(
                        $('<label/>',{
                            'for':'selectColumns'+table+columns,
                            text:columns,
                        })
                    ).append(
                        $('<div/>',{
                        }).addClass('divWhere').addClass('div'+table+columns).append(
                            $('<span/>',{text:'Where: '})
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
                var columnForTextBox = $(this).parent().parent().children("input[type='hidden']").val();
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
                            var columnForTextBox = $(this).parent().parent().children("input[type='hidden']").val();
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
        function HandleWhereUrl(table){
            $('.div'+table).each(function(){
                if ($(this).children('.divWhere').children('select').val()!='' && $(this).children('.divWhere').children('.whereInput'+table).val()!=''){
                    console.log('sdad');
                    whereUrl += '&where['+Number(whereCount)+'][]='+table;
                    whereUrl += '&where['+Number(whereCount)+'][]='+$(this).children('.selectColumns'+table).val();
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
                            whereUrl += "&where["+Number(whereCount)+"][]='"+$(this).children('.divWhere').children('.whereInput'+table).val()+"'";
                        }else {
                            whereUrl += "&where["+Number(whereCount)+"][]="+$(this).children('.divWhere').children('.whereInput'+table).val();
                        }
                    }
                    whereUrl += '&where['+Number(whereCount)+'][]=0';
                    whereCount++;
                }
            });
        }
        $(document).ready(function(){
            SelectWhereChoices('#deleteWhere','<?php echo $_GET['table'] ?>')
            $('#submit').on('click',function(){
                HandleWhereUrl('<?php echo $_GET['table'] ?>');
                console.log('dsf');
                whereUrl = whereUrl.substring(1);
                if (whereCount > 0){
                    var oldUrl = window.location.href;
                    var withoutQuery = oldUrl.substring(0,oldUrl.lastIndexOf('/'));
                    var url = withoutQuery+'/Delete.php?database=<?php echo $_GET['database'] ?>&';
                    
                    
                    url += 'from=<?php echo $_GET['table'] ?>&'+whereUrl;
                    window.location.href = url;
                }
            });
        });
    </script>
</body>
</html>