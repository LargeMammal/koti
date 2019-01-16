<?php
include_once "php/server/server.php";
include_once "php/server/file.php";
include_once "php/server/upload.php";

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
$server = new Server;
$server->serve($config, $method, $items, $lang);
// Do something with those variables
echo $str;
?>
