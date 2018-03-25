<?php 
// 
include "php/loadAll.php";
include "php/miscellaneous/file.php";

$config = loadJSON("/config/default-config.json");
if ($config["err"] != "") {
    echo "Error occured: " . $config["err"];
} else {
    echo "Data: " . $config["data"]["Localhost"]["Site"];
}
echo loadAll($config);
?>