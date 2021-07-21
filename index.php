<?php
/**
 * index.php
 * Everything is directed right here and 
 * request is then built here
 */
function autoloader($class) {
        require_once __DIR__."/classes/" . $class . '.class.php';
}

set_exception_handler(function($exception) {
        echo "<b>Exception:</b> ", $exception->getMessage();
});
if (!spl_autoload_register('autoloader')) 
        trigger_error("Autoloader error");
http_response_code(200);

// Serve
$server = new Server($_SERVER, $_GET, $_POST);
if ($server->error !== NULL) echo $server->error;
echo $server->Serve();
?>
