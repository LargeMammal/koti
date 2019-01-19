<?php
include_once "php/loadSite.php";
include_once "php/server/file.php";
include_once "php/server/server.php";

// Get json array from json file
$config = loadJSON("config/default-config.json");
// Get language from browser
$langs = parseLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);
// Load index site with config data
$items = "not_index";
$item = NULL;

// Upload site is a special case
$str = loadSite($config, $langs, $items, $item);
echo $str;
?>
