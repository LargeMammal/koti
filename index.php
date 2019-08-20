<?php
function autoloader($class) {
    require_once __DIR__."/classes/" . $class . '.class.php';
}

// Error handling test
function myErrorHandler($errLvl, $errMsg, $errFile, $errLine, $errCon) {
    echo "<b>Error: </b> [$errLvl] '$errMsg' in $errFile line $errLine with values: $errCon<br>";
    die(); // If not fatal then don't die
    /**
     * What should happen is when error is encountered this gets called and is shown on the browser.
     * 
     * I hope that later all errors to be saved into database for later usage.
     * Later This would become pipeline for analytics.
     */
}

error_reporting(E_ALL | E_STRICT);
set_error_handler("myErrorHandler");
if (!spl_autoload_register('autoloader')) 
    trigger_error("Autoloader error");
define("CONFIG", __DIR__."/configs/localhost-config.json");

// build variables
$method = $_SERVER['REQUEST_METHOD'];
$langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$uri = $_SERVER['REQUEST_URI'];
$post = $_POST;
$uid = NULL;
$pw = NULL;

if (isset($_SERVER['PHP_AUTH_USER'])) $uid = $_SERVER['PHP_AUTH_USER'];
if (isset($_SERVER['PHP_AUTH_PW'])) $pw = $_SERVER['PHP_AUTH_PW'];

// Serve
$server = new Server(CONFIG, $method, $langs, $uri, $post);
$server->Authorize($uid, $pw);
$str = $server->Serve();
echo $str;
?>
