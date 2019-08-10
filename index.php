<?php
define("CONFIG", __DIR__."/configs/localhost-config.json");

function autoloader($class) {
    require_once __DIR__."/classes/" . $class . '.class.php';
}

// parseObject recursively reads through object vars
// and returns them in an array. Runs only 20 layers deep.
function parseObject($obj, $i = 0) {
    $output = [];
    // Don't go deeper than 20
    if ($i > 20) {
        return $output;
    }
    foreach ($obj as $key=>$val) {
        if (is_object($val)) {
            $output[$key] = parseObject($val, ($i+1));
        } else {
            $output[$key] = $val;
        }
    }
    return $output;
}

// loadFile gets file and returns contents in an array
function loadJSON($file) {
    $pwd = $file;
    if (!file_exists($pwd)) return FALSE;
    $json = file_get_contents($pwd); // reads file into string
    $data = json_decode($json); // turns json into php object
    return parseObject($data);
}

error_reporting(E_STRICT);
spl_autoload_register('autoloader');
$config = loadJSON(CONFIG);
die(var_dump($config));
$DB = new DB($config);
$errors = new Error($DB);
set_error_handler($errors->LogError);
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
$server = new Server($DB, $config, $method, $langs, $uri, $post);
$server->Authorize($uid, $pw);
$str = $server->Serve();
echo $str;
?>
