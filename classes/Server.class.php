<?php
/** 
 * Server.class.php holds Server class
 * Server object will handle http methods
 */

class Server {
        private $config;
        private $db;
        private $oldErrorHandler;
        private $startTime;

        private $auth;
        private $items;
        private $langs;
        private $method;
        private $post;
        private $pw;
        private $realm;
        private $uid;

        function __construct($config, $time, $server, $post = NULL) {
                $this->startTime = $time;
                $timer = round(microtime(true) * 1000); // Start benchmark
                $this->config = $this->loadJSON($config);
                $this->db = new DB($this->config);
                $this->db->LogEvent(
                        E_USER_NOTICE, 
                        "Benchmark: Initialisation took ".
                                (round(microtime(true) * 1000)-$time).
                                " milliseconds", 
                        "non", 
                        0, 
                        0
                );
                // Exception and error handling
                $this->oldErrorHandler = set_error_handler(function(
                                $errLvl, 
                                $errMsg, 
                                $errFile, 
                                $errLine, 
                                $errCon
                                ) {
                                return $this->db->LogEvent(
                                        $errLvl, 
                                        $errMsg, 
                                        $errFile, 
                                        $errLine, 
                                        $errCon
                                );
                });
                set_exception_handler(function($exception) {
                        return $this->db->LogEvent(
                                $exception->getCode(), 
                                $exception->getMessage(), 
                                $exception->getFile(), 
                                $exception->getLine(), 
                                "exception"
                        );
                });
                $this->items = $this->paths($server['REQUEST_URI']);
                if (isset($server['HTTP_ACCEPT_LANGUAGE'])){
                        $lang =$this->getLang($server['HTTP_ACCEPT_LANGUAGE']);
                        $this->langs = $lang;
                } else $this->langs = ["fi-FI"];
                
                $this->method = $server['REQUEST_METHOD'];
                $this->post = $post;
                if (count($post) < 1) $post["Date"] = time();
                $this->pw = NULL;
                $this->uid = NULL;
                if (isset($server['PHP_AUTH_USER']) && 
                        isset($server['PHP_AUTH_PW'])) {
                        $this->pw = $server['PHP_AUTH_PW'];
                        $this->uid = $server['PHP_AUTH_USER'];
                }
                $this->db->LogEvent(
                        E_USER_NOTICE, 
                        "Benchmark: Server construction took ". 
                                (round(microtime(true) * 1000)-$timer).
                                " milliseconds");
        }

        function __destruct() {
                $this->config = NULL;
                $this->db = NULL;
                $this->oldErrorHandler = NULL;
                $this->items = NULL;
                $this->langs = NULL;
                $this->method = NULL;
                $this->post = NULL;
                $this->pw = NULL;
                $this->realm = NULL;
                $this->uid = NULL;
        }

        public function Serve() {
                $timer = round(microtime(true) * 1000);
                $output = "";
                $str = "";
                $level = 2;
                if ($this->items[0] == 'users') $level = 0;
                if ($level > 0) {
                        if (!isset($this->uid)) {
                                header('WWW-Authenticate: Basic realm="'.
                                        $this->realm.'"');
                                http_response_code(401);
                                trigger_error(
                                        "User: ".$this->uid." unauthorized", 
                                        E_USER_ERROR
                                );
                                die("Unauthorized!");
                        }
                        $this->authorize();
                }

                switch($this->method) {
                case 'GET':
                        $site = new Site($this->db,$this->langs,$this->items);
                        $output = $site->Build($this->auth, $this->realm);
                        break;
                case 'POST':
                        if (count($this->post) < 1) break;
                        if (isset($this->post["uid"])) {
                                $users = [
                                        'UID' => $this->post['uid'],
                                        'PW' => $this->post['pw'],
                                        'Mail' => $this->post['email'],
                                        'Date' => time(),
                                        'Auth' => 0,
                                        'Verified' => 0,
                                ];
                                if (isset($this->post["name"])) 
                                        $users['Name'] = $this->post['name'];
                                $this->db->SetItem("users", $users);
                        } else {
                                // split the upload into category and contents.
                                $category = [
                                        'Auth'=>0,
                                        'Category'=>$this->post['Category'],
                                        'Translation'=>$this->post['Translation'],
                                        'Language'=>$this->post['Language']
                                ];
                                unset($this->post['Translation']);
                                $this->db->SetItem('category', $category);
                                $this->db->SetItem('content', $this->post);
                        }
                        break;
                default:
                        header('HTTP/1.1 405 Method Not Allowed');
                        header('Allow: GET POST');
                        break;
                }
                $this->db->LogEvent(
                        E_USER_NOTICE, 
                        "Benchmark: Serve method took ". 
                                (round(microtime(true) * 1000)-$timer).
                                " milliseconds"
                        );
                return $output;
        }

        /** 
         * authorize queries db for user name and password hash.
         * It then compares the two and returns authorization.
         */
        private function authorize() 
        {
                $query = [
                        "Table" => "users",
                        "UID" => $this->uid,
                ];
                $auth = $this->db->GetItem($query); // Get user data
                $pwa = "non";
                $autha = 0;
                if (count($auth) > 0) {
                        $pwa = $auth[0]["PW"];
                        $autha = $auth[0]["Auth"];
                }
                if (!password_verify($this->pw, $pwa)) 
                        $this->auth = $autha;
        }

        private function getLang($str) {
            $output = [];
            // Split the string
            $arr = explode(";", $str);
            foreach ($arr as $value) {
                    // Ignore q thingys
                    foreach (explode(",", $value) as $val) {
                            if (false === strpos($val, "q=")) {
                                    $output[] = $val;
                                    break;
                            }
                    }
            }
            return $output;
        }

        /**
         * parseObject recursively reads through object vars
         * and returns them in an array. Runs only 20 layers deep.
         */
        private function parseObject($obj, $i = 0) {
                $output = [];
                // Don't go deeper than 20
                if ($i > 20) {
                        return $output;
                }
                foreach ($obj as $key=>$val) {
                        if (is_object($val)) 
                                $output[$key] =$this->parseObject($val,($i+1));
                        else $output[$key] = $val;
                }
                return $output;
        }
        
        /**
         * loadFile gets file and returns contents in an array
         */
        private function loadJSON($file) {
                $pwd = $file;
                if (!file_exists($pwd)) return FALSE;
                $json = file_get_contents($pwd); // reads file into string
                $data = json_decode($json); // turns json into php object
                return $this->parseObject($data);
        }

        private function createItem(){
                if (isset($this->contents[$items])) {
                        header('HTTP/1.1 409 Conflict');
                        return;
                }
                /* 
                 * PUT requests need to be handled by reading from standard 
                 * input. php://input is a read-only stream that allows you 
                 * to read raw data from the request body.
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

        private function paths($url) {
                // Remove slashes from both sides.
                $str = trim($url, "/");
                // Explode path into variables
                $items = explode("/", $str);
                return $items;
        }
}
?>
