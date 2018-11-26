<?php
require 'config.php';

$mysql_conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if ($mysql_conn->connect_error) {
        die("Connection failed: " . $mysql_conn->connect_error);
} 
?>
