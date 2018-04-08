<?php
include_once "php/loadAll.php";
include_once "php/miscellaneous/file.php";
include_once "php/miscellaneous/lang.php";

/* if (!function_exists('mysqli_init') && !extension_loaded('mysqli')) {
    echo 'We don\'t have mysqli!!!';
} else {
    echo 'Phew we have it!';
} */

// Get json array from json file
$config = loadJSON("/config/default-config.json");
// Get language from browser
$lang = parseLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);
// Load index site with config data
$site = ["index"];
$str = loadAll($config, $site, $lang);
echo $str;
?>