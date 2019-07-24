<?php
// api.php handles rest calls for now
define("CONFIG", "config/default-config.json");
//define("CONFIG", "config/test-config.json");

function autoloader($class) {
    require_once __DIR__."/classes/" . $class . '.class.php';
}

spl_autoload_register('autoloader');
// build variables
$realm = "Tardiland";
$method = $_SERVER['REQUEST_METHOD'];
$langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$uri = $_SERVER['REQUEST_URI'];
$post = $_POST;
//foreach ($_POST as $key => $value) echo $key.": ".$value;
$uid = NULL;
$pw = NULL;
if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if (isset($_SERVER['PHP_AUTH_PW'])) $pw = $_SERVER['PHP_AUTH_PW'];
// Serve
$server = new Server($realm, $method, $langs, $uri, $post);
// Get json array from json file
$server->LoadJSON(CONFIG);
// Get language from browser
$server->GetLang($langs);
$server->Authorize($uid, $pw);
$str = $server->Serve();
// Do something with those variables
echo $str;
?>
