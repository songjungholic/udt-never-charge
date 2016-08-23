<?
include_once ("config.php");

    $conn = mysql_connect($db_host, $db_id, $db_pw);
    mysql_select_db($db_name,$conn);
?>
