<?php
/** server.php holds Server class
* Server object will handle http methods
*/
// Depending on database call specific library
include_once "php/db/db.php";
include_once "php/loadSite.php";

class Server {
    protected $method;
    protected $items;
    protected $config;
    protected $langs;

    function __construct($method, $langs, $uri) {
        $this->method = $method;
        $this->langs = $this->GetLang($langs);
        $this->items = $this->paths($uri);
    }

    function __destruct() {
        $this->config = NULL;
        $this->langs = NULL;
    }

    public function Serve() {
        $str = "";
        if (count($this->items) > 1) {
            $str = $this->handleItem();
        } else {
            $str = $this->handleItems();
        }
        return $str;
    }

    public function GetLang($str) {
        $output = [];
        // Split the string
        $arr = explode(";", $str);
        foreach ($arr as $value) {
            // ignore q thingys
            foreach (explode(",", $value) as $val) {
                if (false === strpos($val, "q=")) {
                    $output[] = $val;
                    break;
                }
            }
        }
        return $output;
    }

    // loadFile gets file and returns contents in an array
    public function LoadJSON($file) {
        $pwd = $this->getRootDir() . "/" . $file;
        if (!file_exists($pwd)) {
            return FALSE;
        }
        $json = file_get_contents($pwd); // reads file into string
        $data = json_decode($json); // turns json into php object
        $output["data"] = $this->parseObject($data);
        $this->config = $output;
        return TRUE;
    }

    private function handleItems() {
        switch($this->method) {
        case 'GET':
            return loadSite($this->config, $this->langs, $this->items[0], $items[1]);
        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET');
            return "";
        }
    }

    private function handleItem() {
        switch($this->method) {
        case 'PUT':
            $this->createItem();
            return "";

        case 'DELETE':
            $this->deleteItem();
            return "";

        case 'GET':
            return $this->displayItem();

        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET, PUT, DELETE');
        }
        return "";
    }

    private function createItem(){
        if (isset($this->contacts[$items])) {
            header('HTTP/1.1 409 Conflict');
            return;
        }
        /* PUT requests need to be handled by reading from standard input.
         * php://input is a read-only stream that allows you to read raw
         * data from the request body.
         */
        $data = json_decode(file_get_contents('php://input'));
        if (is_null($data)) {
            header('HTTP/1.1 400 Bad Request');
            $this->result();
            return;
        }
        $this->contacts[$items] = $data;
        $this->result();
    }

    private function deleteItem() {
        if (isset($this->contacts[$items])) {
            unset($this->contacts[$items]);
            $this->result();
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

    private function displayItem() {
        return loadSite($this->config, $this->langs, $this->items[0], $items[1]);
    }

    private function paths($url) {
        // Remove slashes from both sides.
        $str = trim($url, "/");
        $items = explode("/", $str); // Explode path into variables
        if (count($items) < 1) {
            $items[] = "not_index";
        } elseif (count($items) > 1) {
            if ($items[0] == "api") {
                $items = array_slice($items, 1);
            }
        }
        return $items;
    }
    // getRootDir gets the root directory of the app.
    // This is useful when you need to access files
    // in highly refactored environment.
    private function getRootDir() {
        // getcwd gives you the working directory
        $path = getcwd();
        // split the path into an array
        $arr = explode("/", $path);
        // initialize output and stop
        $output = array();
        $stop = false;
        // check each value for
        foreach ($arr as $value) {
            switch($value) {
                case "php":
                case "projects":
                    $stop = true;
                default:
                    array_push($output, $value);
            }
            if ($stop == true) { // stop when root is found
                break;
            }
        }
        // implode the array back into string
        return implode("/", $output);
    }

    // parseObject recursively reads through object vars
    // and returns them in an array. Runs only 20 layers deep.
    // Recursiot on perseestä. Koita muuttaa iteratiiviseksi.
    private function parseObject($obj, $i = 0) {
        $output = [];
        // Don't go deeper than 20
        if ($i > 20) {
            return $output;
        }
        foreach ($obj as $key=>$val) {
            if (is_object($val)) {
                $output[$key] = $this->parseObject($val, ($i+1));
            } else {
                $output[$key] = $val;
            }
        }
        return $output;
    }

    // logging saves everything into a specific file
    private function logging($name = "koti_log.log") {
        // Reports all errors
        error_reporting(E_ALL);
        // Do not display errors for the end-users (security issue)
        ini_set('display_errors','Off');
        // Set a logging file
        ini_set('error_log',$name);


        // Override the default error handler behavior
        set_exception_handler(function($exception) {
           error_log($exception);
           error_page("Something went wrong!");
        });
    }
}
?>
