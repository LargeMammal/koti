<?php
include_once "php/loadSite.php";
include_once "php/miscellaneous/file.php";
include_once "php/miscellaneous/lang.php";
include_once "php/miscellaneous/upload.php";

// Get json array from json file
$config = loadJSON("/config/default-config.json");
// Get language from browser
$lang = parseLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);

// Listen to posts
if (isset($_POST)) {
    $table = "";
    if (isset($_POST['table'])) {
        $table = $_POST['table'];
        unset($_POST['table']);
    } else {
        foreach($_POST as $key=>$val){
            if ($key != 'lang') {
                $table = $key;
            }
        }
    }
    // Upload post stuff
    $err = upload($config, $table, $_POST);
    // Parse all errors
    if (count($err) > 0) {
        foreach($err as $error) {
            $config["err"][] = $error;
        }
    }
}

// Get values from URI
$str = $_SERVER["PATH_INFO"];
// Remove slashes from both sides. 
$str = trim($str, "/");
$site = explode("/", $str); // Explode path into variables
// Upload site is a special case
$str = loadSite($config, $site, $lang);
// Do something with those variables
echo $str;
?>