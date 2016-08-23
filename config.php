<?
$db_host = "52.78.129.66";
$db_id = "udt";
$db_pw = "udt";
$db_name = "udt"; 
$db_con = mysql_connect($db_host, $db_id, $db_pw);
mysql_select_db($db_name, $db_con) or die("DB error");
?>

