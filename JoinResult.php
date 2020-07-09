<?php
    session_start();

    echo "Query:<br/><br/>";
    print_r($_SESSION['query']['query']);
    echo "<br/><br/><br/>";
?>
<!DOCTYPE html>
<html>
<head><title>Join Result</title></head>
<body>
    <table>
        <tr>
    <?php
          foreach ($_SESSION['query']['columnName'] as $key=>$value) : ?>
            <th><?php echo $value; ?></th>
    <?php endforeach; ?>
        </tr>
    <?php
          foreach ($_SESSION['query']['record'] as $key1=>$value1) : ?>
        <tr>
    <?php
            foreach ($value1 as $key2=>$value2) : ?>
            <td><?php echo $value2 ?></td>
    <?php
            endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </table>
</body>
</html>