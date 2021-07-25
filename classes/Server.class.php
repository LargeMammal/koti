<?php
/** 
 * Server.class.php holds Server class
 * Server object will handle http methods
 */

class Server {
        public $error;

        private $method;
        //private $post;
        private $get;
        
        private $contents;
        private $items;
        private $db;
        private $type; // html/json/xml
        private $server;

        /**
         * @brief
         * Site object gathers all information needed to handle a request
         * 
         * @param DB my database object that creates 
         * relatively easy database interface
         * @param array assoc array of server parameters
         * @param array assoc array of get variables
         * @param array assoc array of post variables
         */
        function __construct(
                array $server, 
                array $get = NULL, 
                array $post = NULL
        ){
                $this->error = NULL;

                $this->method = $server['REQUEST_METHOD'];
                //$this->post = $post;
                $this->get = $get;

                $this->contents = [];
                $this->items = [];
                $this->db = new DB();
                $this->type = "html";
                $this->server = $this->paths($server['REQUEST_URI']);
                if ($this->server === NULL) $this->server = ["title", "index"];
                $n = count($this->server);
                if (($n > 1) && ($n % 2 !== 0))
                        $this->error = "Malformed request!";
        }
        
        function __destruct() 
        {
                $this->method = NULL;
                $this->post = NULL;
                $this->get = NULL;

                $this->contents = NULL;
                $this->items = NULL;
                $this->db = NULL;
                $this->type = NULL;
                $this->server = NULL;
        }

        public function Serve() {
                $output = "";
                switch($this->method) {
                case 'GET':
                        $output = $this->get();
                        break;
                case 'POST':
                        $output = $this->post();
                        break;
                case 'DELETE':
                        $this->delete();
                        break;
                default:
                        http_response_code(405);
                        header('Allow: GET POST');
                        break;
                }
                return $output;
        }
        
        /**
         * @brief
         * Get function handles get requests. Adds necessary headers.
         * More accurately it formats the request. Request is done in 
         * construction. 
         * @return string Get function returns site in string form
         */
        private function get()
        {
                $token = $this->get['token'];
                $this->contents = $this->db->DBGet($this->server);
                if (count($this->contents) < 1) 
                        $this->contents = NULL;

                switch ($this->type) {
                        case 'json':
                                header('Content-Type: application/json');
                                return json_encode($this->contents);
                        case 'xml':
                                header('Content-Type: text/xml');
                                $xml = new SimpleXMLElement('<root/>');
                                array_flip($this->contents);
                                array_walk_recursive($this->contents, array($xml, 'addChild'));
                                return $xml->asXML();
                        default:
                                # code...
                                break;
                }

                // Stuff in head
                $str = '<!DOCTYPE html><head>';
                $str .= $this->loadHead();
                $str .= '</head>';
                // Stuff in body
                $str .= '<body><div id="root"><div><header>'.$this->loadHeader();
                $str .= '<nav>'.($this->db->DBGet(["title", "nav"]))[0].'</nav></header>';
                $str .= '<section>'.$this->loadBody().'</section>';
                $str .= '<footer>'.($this->db->DBGet(["title", "footer"]))[0].'</footer>';
                $str .= '</div></div></body></html>';
                return $str;
        }

        /**
         * @brief
         * Post function handles post requests.
         * @return string error string. NULL if no errors
         */
        private function post(): string
        {
                echo var_dump($_POST);
                if (empty($_POST)) return 'Empty request';
                if ($_POST["token"] === NULL) return 'Missing token';
                if ((count($this->server) < 2) || $this->server[0] !== 'title')
                        return 'Malformed request';

                // Get token id pair that matches token in request
                $token = $this->db->DBGetToken($_POST['token']);
                if ($this->db->error !== NULL) {
                        http_response_code(500);
                        return $dbitem->error;
                }
                if ($token['user'] !== $uname) {
                        http_response_code(403);
                        return "Wrong token";
                }
                $query = NULL;
                $query['title'] = $this->server[1];
                $query['user'] = $token['user']; // token 'user' is just user id number
                $query['item'] = $_POST['item'];
                $query['auth'] = $_POST['auth'];
                $query['tags'] = $_POST['tags'];
                $dbitem = new DBItem($query);
                if ($dbitem->error !== NULL) {
                        http_response_code(500);
                        return $dbitem->error;
                }
                if (!$this->db->DBPost($dbitem)) {
                        http_response_code(500);
                        return "Failed the request: ". $this->db->error;
                }
                return NULL;
        }

        /**
         * Delete function handles delete requests.
         * 
         * @return void Delete only generates response code
         */
        private function delete()
        {
        }

        /** 
         * loadHead function should generate the head elements.
         * Later I should add meta handling
         */
        private function loadHead() 
        {
                $title = $this->items['Table'];
                if (isset($this->contents))
                        if (count($this->contents) == 1)
                                $title = $this->contents[0]['Title'];
                                
                $str = '<meta charset="UTF-8">';
                $str .= "<title>$title</title>";
                $str .= '<meta name="viewport" 
                        content="width=device-width, initial-scale=1.0">';
                $str .= '<link rel="icon" type="image/png" href="/image.png">';
                $str .= '<link rel="stylesheet" type="text/css" 
                        href="/css/common.css" >';
                return $str;
        }

        /** 
         * loadHeader will in future generate custom header
         */
        private function loadHeader() 
        {
                $banner = "";
                if (is_null($this->contents)) 
                        return "<h1>".$this->items['Title']."</h1>"; 
                if (count($this->contents) == 1) 
                        $banner = $this->contents[0]['Title'];
                $output = "<h1>$banner</h1>";
                return $output;
        }

        /** loadBody
         * loadBody will generate content section of the page
         */
        private function loadBody() 
        {
                $content = "";
                if (is_null($this->contents) || count($this->contents) < 1) 
                        return "<h1>Site came up empty!</h1>";
                foreach ($this->contents as $items) {
			$content .= "<section>";
			$content .= $items['Content'];
			$content .= "</section>";
                }
                return $content;
        }

        /**
         * paths extracts file path and get variables from URI
         * 
         * @param string URI in string form 
         * @return array returns array of URI elements. 
         */
        private function paths($url):array
        {
                if ($url === '') return NULL;
                // Get get variables. 
                $vget = explode('?', trim($url, "/"));
                $type = explode('.', $vget[0]);
                if ($vget[0] == "") return [];
                if (count($type) > 1) {
                        switch ($type[count($type)-1]) {
                        case 'json':
                                $this->type = 'json';
                                unset($type[count($type)-1]);
                                $vget[0] = implode('.', $type);
                                break;
                        case 'xml':
                                $this->type = 'xml';
                                unset($type[count($type)-1]);
                                $vget[0] = implode('.', $type);
                                break;
                        case 'test':
                                $this->type = 'test';
                                unset($type[count($type)-1]);
                                $vget[0] = implode('.', $type);
                                break;
                        default:
                                throw new Exception("file type not recognized");
                                break;
                        }
                }
                $items = explode("/", $vget[0]);
                return $items;
        }
}
?>
