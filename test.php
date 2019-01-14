<?php
/** test.php:
* test.php file will hold tests.
* I aim to create this so that this can be run offline.
*/
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
