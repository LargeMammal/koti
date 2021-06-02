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

        private $method;
        private $site;

        function __construct($config, $time, $server, $get = NULL, $post = NULL) {
                $this->startTime = $time;
                $timer = round(microtime(true) * 1000); // Start benchmark
                $this->config = $this->loadJSON($config);
                $this->db = new DB($this->config);
                /*
                $this->db->LogEvent(
                        E_USER_NOTICE, 
                        "Benchmark: Initialisation took ".
                                (round(microtime(true) * 1000)-$time).
                                " milliseconds", 
                        "non", 
                        0, 
                        0
                );
                //*/
                $this->method = $server['REQUEST_METHOD'];
                // Exception and error handling
                /* 
                $this->oldErrorHandler = set_error_handler(
                        function(
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
                }); // */
                set_exception_handler(function($exception) {
                        return $this->db->LogEvent(
                                $exception->getCode(), 
                                $exception->getMessage(), 
                                $exception->getFile(), 
                                $exception->getLine(), 
                                "exception"
                        );
                });
                /*
                $this->db->LogEvent(
                        E_USER_NOTICE, 
                        "Benchmark: Server construction took ". 
                                (round(microtime(true) * 1000)-$timer).
                                " milliseconds");
                //*/
                $this->site = new Site($this->db, $server, $get, $post);
        }

        function __destruct() {
                $this->config = NULL;
                $this->db = NULL;
                $this->oldErrorHandler = NULL;
                $this->method = NULL;
                $this->post = NULL;
                $this->site = NULL;
        }

        public function Serve() {
                $timer = round(microtime(true) * 1000);
                $output = "";
                $str = "";
                switch($this->method) {
                case 'GET':
                        $output = $this->site->Get();
                        break;
                case 'POST':
                        $this->site->Post();
                        break;
                case 'DELETE':
                        $this->site->Delete();
                        break;
                default:
                        http_response_code(405);
                        header('Allow: GET POST DELETE');
                        break;
                }
                /*
                $this->db->LogEvent(
                        E_USER_NOTICE, 
                        "Benchmark: Serve method took ". 
                                (round(microtime(true) * 1000)-$timer).
                                " milliseconds"
                        );
                //*/
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
         * loadJSON gets JSON file and returns contents in an array
         */
        private function loadJSON($file) {
                $pwd = $file;
                if (!file_exists($pwd)) return FALSE;
                $json = file_get_contents($pwd); // reads file into string
                $data = json_decode($json); // turns json into php object
                return $this->parseObject($data);
        }
}
?>
