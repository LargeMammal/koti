<?php
include_once "php/loadSite.php";
include_once "php/miscellaneous/file.php";
include_once "php/miscellaneous/lang.php";

// Get json array from json file
$config = loadJSON("/config/default-config.json");
// Get language from browser
$lang = parseLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);
// Load index site with config data
$site = ["index"];
$str = loadSite($config, $site, $lang);
echo $str;
?>