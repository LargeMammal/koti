<?php
include_once "php/loadSite.php";
include_once "php/miscellaneous/file.php";
include_once "php/miscellaneous/lang.php";

// Get json array from json file
$config = loadJSON("/config/default-config.json");
// Get language from browser
$lang = parseLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);

if (isset($_POST['title'])) {
    $config['err'][] = "We got post";
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Get values from URI
    $str = $_SERVER["PATH_INFO"];
    // Remove slashes from both sides. 
    $str = trim($str, "/");
    $site = explode("/", $str); // Explode path into variables
    // Upload site is a special case
    $str = loadSite($config, $site, $lang);
    // Do something with those variables
    echo $str;
}
?>