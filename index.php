<?php
/**
 * index.php
 * Everything is directed right here and 
 * request is then built here
 */
$time = round(microtime(true) * 1000);
define("CONFIG", __DIR__."/configs/localhost-config.json");

function autoloader($class) {
        require_once __DIR__."/classes/" . $class . '.class.php';
}
 
/**
 * I hope that later all errors to be saved into database for later usage.
 * Later This would become pipeline for analytics.
 */
error_reporting(E_ALL);
set_error_handler(function($errLvl, $errMsg, $errFile, $errLine, $errCon) {
        ob_start();
        debug_print_backtrace();
        $dump = ob_get_clean();
        echo "<b>Error: </b> [$errLvl] '$errMsg' in $errFile line". 
                "$errLine<br><pre>$dump</pre>";
        if ($errno == E_ERROR || $errno == E_USER_ERROR) die();
});
set_exception_handler(function($exception) {
        echo "<b>Exception:</b> ", $exception->getMessage();
});
if (!spl_autoload_register('autoloader')) 
        trigger_error("Autoloader error");

// Serve
$server = new Server(CONFIG, $time, $_SERVER, $_POST);
echo $server->Serve();
?>
