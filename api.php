<?php
include_once "php/server/server.php";
include_once "php/server/file.php";
// Get json array from json file
$config = loadJSON("config/default-config.json");
// Get language from browser
$langs = parseLang($_SERVER['HTTP_ACCEPT_LANGUAGE']);

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
if (count($items) < 1) {
    $items[] = "not_index";
} elseif (count($items) > 1) {
    if ($items[0] == "api") {
        $items = array_slice($items, 1);
    }
}
// Serve
//echo implode($items);
$server = new Server;
$str = $server->serve($config, $langs, $method, $items);
// Do something with those variables
echo $str;
?>
