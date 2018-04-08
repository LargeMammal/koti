<?php
include_once "php/loadAll.php";
include_once "php/miscellaneous/file.php";
include_once "php/miscellaneous/lang.php";
include_once "php/miscellaneous/upload.php";

/* if (!function_exists('mysqli_init') && !extension_loaded('mysqli')) {
    echo 'We don\'t have mysqli!!!';
} else {
    echo 'Phew we have it!';
} */

// Get json array from json file
$config = loadJSON("/config/default-config.json");
// Get language from browser
$lang = parseLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get values from URI
    $str = $_SERVER["PATH_INFO"];
    // Remove slashes from both sides. 
    $str = trim($str, "/");
    $site = explode("/", $str); // Explode path into variables
    // Split into multiple paths
    if ($site[0] === "upload") {
        $str = loadSaveSite($config, $site, $lang);
    } else {
        $str = loadAll($config, $site, $lang);
    }
    // Do something with those variables
    echo $str;
}
?>