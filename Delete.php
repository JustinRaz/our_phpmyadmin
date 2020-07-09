<?php
    session_start();

    $databaseUrl = empty($_GET['database']) ? NULL : $_GET['database'];
    $deleteUrl = empty($_GET['delete']) ? NULL : $_GET['delete'];
    $fromUrl = empty($_GET['from']) ? NULL : $_GET['from'];
    $idKey = empty($_GET['idKey']) ? NULL : $_GET['idKey'];
    $idValue = empty($_GET['idValue']) ? NULL : $_GET['idValue'];
    $whereUrl = empty($_GET['where']) ? NULL : $_GET['where'];
    $query;
    
    $conn = new mysqli('localhost', 'root', '', $databaseUrl);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if (gettype($whereUrl) != 'string'){
        if (isset($idKey) && isset($idValue) && isset($fromUrl)){
            $formattedWhere = 'WHERE ';
            $PKlength = count($idKey)==count($idValue) ? count($idKey) : 0;
            for ($i=0 ; $i<$PKlength ; $i++){
                $idValue[$i] = "'{$idValue[$i]}'";
                $formattedWhere .= " {$idKey[$i]}={$idValue[$i]} AND ";
            }
        }else if (is_array($whereUrl)){
            //multi delete
            $formattedWhere = 'WHERE';
            
            foreach ($whereUrl as $key=>$value){
                $formattedWhere .= " {$value[1]} {$value[2]} {$value[3]} AND ";
            }
        }
        $formattedWhere = substr($formattedWhere,0,-4);
        $query = "DELETE FROM {$fromUrl} {$formattedWhere}";
        
        $conn->query($query);
        if ($conn->error){
            print_r($conn->error);
            die();
        }
        header('Location: '.$_SERVER['HTTP_REFERER']);
    }else if (gettype($whereUrl) == 'string') {
        if ($whereUrl == 'database'){
            $query = "DROP DATABASE {$databaseUrl}";
        }else if ($whereUrl == 'drop'){
            $query = "DROP TABLE {$fromUrl}";
        }else if ($whereUrl == 'truncate'){
            $query = "TRUNCATE TABLE {$fromUrl}";
        }
        print_r($query);
        $conn->query($query);
        if ($conn->error){
            print_r($conn->error);
            die();
        }
        header('Location: '.$_SERVER['HTTP_REFERER']);
    }
    
?>