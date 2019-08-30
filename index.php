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

function myErrorHandler($errLvl, $errMsg, $errFile, $errLine, $errCon) {
    echo "<b>Error: </b> [$errLvl] '$errMsg' in $errFile line $errLine with values: $errCon<br>";
    die(); 
    /**
     * What should happen is when error is encountered this gets called and is shown on the browser.
     * 
     * I hope that later all errors to be saved into database for later usage.
     * Later This would become pipeline for analytics.
     */
}

error_reporting(E_ALL | E_STRICT);
set_error_handler("myErrorHandler");
if (!spl_autoload_register('autoloader')) trigger_error("Autoloader error");

// Serve
$server = new Server(CONFIG, $_SERVER, $_POST);
echo $server->Serve();
?>
