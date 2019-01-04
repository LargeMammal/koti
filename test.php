<?php
include_once "php/db/db.php";
echo "testing";
$config = "";
$table = "test";
// Create table
setItem($config, $table);
// Get table
$table = getItem();
// Remove table
removeItem($table);
?>
