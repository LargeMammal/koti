<?php
/** server.php holds Server class
* Server object will handle http methods
*/
include_once "db/db.php";
include_once "db/initialise.php";

class Server {
    private $uid;
    private $pw;
    protected $realm;
    protected $method;
    protected $items;
    protected $config;
    protected $langs;
    protected $post;

    function __construct($realm, $method, $langs, $uri, $post) {
        $this->realm = $realm;
        $this->method = $method;
        $this->langs = $this->GetLang($langs);
        $this->items = $this->paths($uri);
        if (isset($post)) $post["Date"] = time();
        $this->post = $post;
    }

    function __destruct() {
        $this->uid = NULL;
        $this->pw = NULL;
        $this->method = NULL;
        $this->items = NULL;
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

    public function Authorize($uid, $pw) {
        $this->uid = $uid;
        $this->pw = $pw;
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
        //$pwd = __DIR__."/" . $file;
        if (!file_exists($pwd)) {
            return FALSE;
        }
        $json = file_get_contents($pwd); // reads file into string
        $data = json_decode($json); // turns json into php object
        $this->config = $this->parseObject($data);
        return TRUE;
    }

    /**
     * This should be removed later
     */
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

    private function handleItems() {
        switch($this->method) {
        case 'GET':
            $site = new Site($this->config, $this->langs, $this->items);
            return $site->Build($this->uid, $this->pw);
        case 'POST':
            // This should take the table element and push it strait to db if user is allowed.
            // Authorize the user
            $query = [
                'Table' => $this->items[0],
                'UID' => $this->uid,
            ];
            $str = "";
            $level = 0;
            if ($this->items[0] == "content") {
                // if content upload set auth level to 2
                $level = 2;
            } elseif ($this->items[0] == "users") {
                if (isset($this->post["pw"])) $this->post["pw"] = password_hash($this->post["pw"], PASSWORD_DEFAULT);
                else $this->post["pw"] = "";
            }
            $auth = getItem($this->config[$this->config["Use"]], $query); // Get user data
            $authorization = 0;
            $pw = "";
            if(isset($auth["data"][0]["Auth"])) $authorization = $auth["data"][0]["Auth"];
            if(isset($auth["data"][0]["PW"])) $pw = $auth["data"][0]["PW"];
            // Fail if incorrect credentials or authorization
            if (!password_verify($this->pw, $pw) && $authorization < $level) {
                    header('WWW-Authenticate: Basic realm="'.$this->realm.'"');
                    header('HTTP/1.0 401 Unauthorized');
                    die("Error: ".$this->post['uid']);
            }
            $err = [];
            if (isset($this->post["uid"])) {
                $users = [
                    'UID' => $this->post['uid'],
                    'PW' => $this->post['pw'],
                    'Mail' => $this->post['email'],
                    'Date' => time(),
                    'Auth' => 0,
                    'Verified' => 0,
                ];
                if (isset($this->post["name"])) $users['Name'] = $this->post['name'];
                $err = initReg($this->config[$this->config["Use"]], $users);
            } else {
                // If ok, proceed with writing
                $str .=$this->config["Use"]."<br>";
                $err = setItem($this->config[$this->config["Use"]], $this->items[0], $this->post);
            }
            if (isset($err)) {
                foreach ($err as $e) {
                    $str .= $e."<br>";
                }
                //http_response_code(500);
            } else {
                http_response_code(201);
            }
            break;
        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET POST');
            break;
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
        $site = new Site($this->config, $this->langs, $this->items);
        return $site->Build($this->uid, $this->pw);
    }

    private function paths($url) {
        // Remove slashes from both sides.
        $str = trim($url, "/");
        if ($str == "") $str = "home";
        // Explode path into variables
        $items = explode("/", $str);
        return $items;
    }
    // getRootDir gets the root directory of the app.
    // This could be obsolete
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