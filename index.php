<?php
// api.php handles rest calls for now
define("CONFIG", "config/default-config.json");
//define("CONFIG", "config/test-config.json");

function autoloader($class) {
    require_once 'classes/' . $class . '.class.php';
}

spl_autoload_register('autoloader');
// build variables
$method = $_SERVER['REQUEST_METHOD'];
$langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$uri = $_SERVER['REQUEST_URI'];
$uid = $_SERVER['PHP_AUTH_USER'];
$pw = $_SERVER['PHP_AUTH_PW'];
// Serve
$server = new Server($method, $langs, $uri);
// Get json array from json file
$server->LoadJSON(CONFIG);
// Get language from browser
$server->GetLang($langs);
$server->Authorize($uid, $pw);
$str = $server->Serve();
// Do something with those variables
echo $str;
?>
