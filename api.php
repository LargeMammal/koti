<?php
include "php/loadAll.php";
include "php/miscellaneous/file.php";
// These are for research purposes.
//$str = "";
//foreach ($_SERVER as $key => $val) {
//    $str .= $key . ":" . $val . "<br>";
//} 

$config = loadFile("config/default-config.json");
if (empty($config["data"])) {
    echo "Error: " . $config["err"] . ", Data: empty<br>";
} else {
    $str = "";
    if (is_array($config)) {
        foreach ($config as $key => $val) {
            $str .= $val;
        }
    } else {
        $str .= "could not read data";
    }
    $str .= "<br>";
    echo $str;
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
            echo loadAll();
    }
    // Do something with those variables
}
?>