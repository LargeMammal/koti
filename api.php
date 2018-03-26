<?php
include "php/loadAll.php";
include "php/miscellaneous/file.php";

$config = loadJSON("/config/default-config.json");
$err = "";
if ($config["err"] != "") {
    $err = "Error occured: " . $config["err"];
} else {
    $err = "Data: " . $config["data"]["Localhost"]["Site"];
}
// Study about cookies and how to load the language from header
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get values from URI
    $str = $_SERVER["PATH_INFO"];
    $arr = explode("/", $str); // Explode path into variables
    // Count the variables
    $len = sizeof($arr);
    // Split into multiple paths
    switch ($len) {
        case 1: // Present the base site
            // Refactor the sites into functions
        case 2: // Load site with two variables
            // somesite()
        default: // The default case: just load the main site.
            echo $err . "<br>" . loadAll($config);
    }
    // Do something with those variables
}
?>