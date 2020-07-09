<?php
    session_start();

    $formattedSelect;
    $formattedFrom;
    $formattedJoin;
    $formattedWhere;
    $formattedGroup;
    $formattedOrder;

    echo "<br/><br/>";
    $databaseUrl = empty($_GET['database']) ? NULL : $_GET['database'];
    $fromUrl = empty($_GET['from']) ? NULL : $_GET['from'];
    $countUrl = empty($_GET['count']) ? NULL : $_GET['count'];
    $selectUrl = empty($_GET['select']) ? NULL : $_GET['select'];
    $whereUrl = empty($_GET['where']) ? NULL : $_GET['where'];
    $joinUrl = empty($_GET['join']) ? NULL : $_GET['join'];
    $groupUrl = empty($_GET['group']) ? NULL : $_GET['group'];
    $orderUrl = empty($_GET['order']) ? NULL : $_GET['order'];

    $error = NULL;
    $error = CheckConnectedness();
    if (!is_null($error)){
        print_r($error);
    }else {
        EncapAlias();

        if (!is_null($countUrl)){
            $formattedSelect = 'COUNT(*),';
        }
        $formattedSelect .= FormatSelect();
        if ($formattedSelect == ''){
            $formattedSelect = '*';
        }else if ($formattedSelect == 'COUNT(*),'){
            $formattedSelect = substr($formattedSelect, 0, -1);
        }
        

        $formattedFrom = FormatFrom();


        if (isset($joinUrl)){
            $formattedJoin = FormatJoin();
        }else {
            $formattedJoin = '';
        }
        
        print_r($formattedSelect);
        print_r($formattedFrom);
        print_r($formattedJoin);
        if (isset($whereUrl)){
            $formattedWhere = FormatWhere();
        }else {
            $formattedWhere = '';
        }

        if (isset($groupUrl)){
            $formattedGroup = FormatGroup();
        }else {
            $formattedGroup = '';
        }
        if (isset($orderUrl)){
            $formattedOrder = FormatOrder();
        }else {
            $formattedOrder = '';
        }

        $query = "SELECT {$formattedSelect} FROM {$formattedFrom} {$formattedJoin} {$formattedWhere} {$formattedGroup} {$formattedOrder}";
        echo "<br/><br/>";
        print_r($query);

        Query($query);
        
        if (isset($_GET['reroute'])){
            header('Location: '.$_GET['reroute']);
        }else {
            header('Location: '.$_SERVER['HTTP_REFERER']);
        }
    }
    function Query($query){
        global $databaseUrl, $joinUrl, $fromUrl;
        $dupli;
        
        $conn = new mysqli('localhost', 'root', '', $databaseUrl);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $dupli = $conn->query($query);
        $_SESSION['query'] = array();
        $_SESSION['query']['query'] = $query;
        $_SESSION['query']['columnName'] = array();
        print_r($conn->error);
        for ($i=0 ; $finfo = $dupli->fetch_field() ; $i++){
            $_SESSION['query']['columnName'][$i] = $finfo->name;
            printf("   %s\n", $finfo->name);
        }
        $_SESSION['query']['record'] = array();
        for ($i=0 ; $row=$dupli->fetch_row() ; $i++) {
            $_SESSION['query']['record'][$i] = array();
            for ($j=0 ; isset($row[$j]) ; $j++){
                $_SESSION['query']['record'][$i][$j] = $row[$j];
            }
        }
    }
    function EncapAlias(){
        global $fromUrl, $selectUrl;
        $alias = 'a';

        $selectUrlLength = count($selectUrl);
        for ($i=0 ; $i<$selectUrlLength ; $i++){
            if ($selectUrl[$i][0]){
                foreach ($selectUrl[$i] as $key=>$value){
                    $selectUrl[$i][$key] = $alias.'.'.$value;
                }
            }
            $alias++;
        }
    }
    function FormatSelect(){
        global $selectUrl;
        $retVal = '';

        foreach ($selectUrl as $key=>$value){
            $retVal .= implode(',',$value);
            $retVal .= ',';
        }
        $retVal = substr($retVal, 0, -1);
        return $retVal;
    }
    function FormatFrom(){
        global $fromUrl;

        return $fromUrl[0]. ' AS a';
    }
    function FormatJoin(){
        global $joinUrl;
        $joinType = '';
        $aliasTo = 'b';
        $aliasFrom;
        $retVal = '';

        foreach ($joinUrl as $key=>$value){
            $aliasFrom = fromNumToAlias($value[3]);
            $joinType = ChooseJoin($value[0]);
            $retVal .= " {$joinType} {$value[4]} AS {$aliasTo} ON 
                        {$aliasTo}.{$value[5]}={$aliasFrom}.{$value[2]}";
            $aliasTo++;
        }
        return $retVal;
    }
    function fromNumToAlias($val){
        $retVal = 'a';

        for ( ; $val>0 ; $val--){
            $retVal++;
        }
        return $retVal;
    }
    function ChooseJoin($urlQuery){
        if ($urlQuery == 'LJ'){
            return 'LEFT JOIN';
        }else if ($urlQuery == 'RJ'){
            return 'RIGHT JOIN';
        }else {
            return 'INNER JOIN';
        }
    }
    function FormatWhere(){
        global $aliasMap, $whereUrl;
        $retVal = 'WHERE';
        $alias;
        foreach ($whereUrl as $key=>$value){
            $alias = fromNumToAlias($value[4]);
            $retVal .= " {$alias}.{$value[1]} {$value[2]} {$value[3]} AND";
        }
        $retVal = substr($retVal,0,-3);
        return $retVal;
    }
    function FormatGroup(){
        global $groupUrl;
        $retVal = '';
        $alias = 'a';
        $nthTable = $groupUrl[2];

        for ($i=0 ; $i<$nthTable ; $i++){
            $alias++;
        }

        $retVal = "GROUP BY {$alias}.{$groupUrl[1]}";
        return $retVal;
    }
    function FormatOrder(){
        global $orderUrl;
        $retVal = '';
        $alias = 'a';
        $nthTable = $orderUrl[2];

        for ($i=0 ; $i<$nthTable ; $i++){
            $alias++;
        }

        $retVal = "ORDER BY {$alias}.{$orderUrl[1]}";
        return $retVal;
    }
    function CheckConnectedness(){
    }
?>