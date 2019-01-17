<?php
include_once "php/server/server.php";
include_once "php/server/file.php";
/*
// Start logging into default// Reports all errors
error_reporting(E_ALL);
// Do not display errors for the end-users (security issue)
ini_set('display_errors','Off');
// Set a logging file
ini_set('error_log','my_file.log');


// Override the default error handler behavior
set_exception_handler(function($exception) {
   error_log($exception);
   error_page("Something went wrong!");
});
//*/
// Get json array from json file
$config = loadJSON("config/default-config.json");
// Get language from browser
$lang = parseLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);

// Get values from URI
$str = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

/* Listen for posts
if (count($_POST) > 0) {
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
//*/

// Remove slashes from both sides.
$str = trim($str, "/");
$items = explode("/", $str); // Explode path into variables
// Serve
//echo implode($items);
$server = new Server;
$server->serve($config, $method, $items, $lang);
// Do something with those variables
echo $str;
?>
