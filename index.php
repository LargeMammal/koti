<?php
/**
 * index.php
 * Everything is directed right here and 
 * request is then built here
 */
define("CONFIG", __DIR__."/configs/localhost-config.json");

function autoloader($class) {
    require_once __DIR__."/classes/" . $class . '.class.php';
}
 
/**
 * I hope that later all errors to be saved into database for later usage.
 * Later This would become pipeline for analytics.
 */
error_reporting(E_ALL | E_STRICT);
set_error_handler(function($errLvl, $errMsg, $errFile, $errLine, $errCon) {
    echo "<b>Error: </b> [$errLvl] '$errMsg' in $errFile line $errLine with values: $errCon<br>";
    die();
});
set_exception_handler(function($exception) {
    echo "<b>Exception:</b> ", $exception->getMessage();
});
if (!spl_autoload_register('autoloader')) trigger_error("Autoloader error");

// Serve
$server = new Server(CONFIG, $_SERVER, $_POST);
echo $server->Serve();
?>
