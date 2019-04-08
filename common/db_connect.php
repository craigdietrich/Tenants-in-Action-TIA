<?php
$link = mysql_connect('SERVER', 'USER', 'PASSWORD');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('DATABASE', $link);
if (!$db_selected) {
    die (mysql_error());
}

header('Content-Type: text/html; charset=utf-8');
mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
?>