<?php
require 'init.php';

foreach (array("online", "f2f") as $flow) {
    $ok = $mysql_conn->query("DROP TABLE IF EXISTS ${flow}_listings;"); 
    if (!$ok) {
        die("couldn't drop table: " . $mysql_conn->error);
    }

    $ok = $mysql_conn->query("CREATE TABLE ${flow}_listings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            descr VARCHAR(255),
            price INT,
            trustap_listing_id VARCHAR(255),
            sold BOOLEAN DEFAULT FALSE,
            joins INT DEFAULT 0
    );");
    if (!$ok) {
        die("couldn't create table: " . $mysql_conn->error);
    }
}

echo 'done.';
?>
