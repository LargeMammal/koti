<?php
/** 
 * Server.class.php holds Server class
 * Server object will handle http methods
 */

class Server {
        private $method;
        private $get;
        private $post;
        
        private $contents;
        private $db;
        private $footer;
        private $type; // html/json/xml
        private $items;
        private $pw;
        private $uid;

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
                $this->auth = 0; // Make sure to purge privileges
                $this->contents = [];
                $this->type = "html";
                $this->db = new DB($this->config);
                $this->server = $this->paths($server['REQUEST_URI']);
                $this->post = $post;
                $this->get = $get;
                $this->items = [];
                $timer = round(microtime(true) * 1000); // Start benchmark
                $this->method = $server['REQUEST_METHOD'];
        }
        
        function __destruct() 
        {
                $this->auth = NULL;
                $this->contents = NULL;
                $this->db = NULL;
                $this->footer = NULL;
                $this->items = NULL;
                $this->config = NULL;
                $this->method = NULL;
                $this->post = NULL;
        }

        public function Serve() {
                $timer = round(microtime(true) * 1000);
                $output = "";
                $str = "";
                switch($this->method) {
                case 'GET':
                        $output = $this->Get();
                        break;
                case 'POST':
                        $this->Post();
                        break;
                case 'DELETE':
                        $this->Delete();
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
                if (count($items) < 1) $items = ["title", "index"];
                $this->items["Table"] = $items[0];
                array_shift($items);
                foreach ($items as $key => $value) {
                        if ($key % 2 == 0) {
                                $name = trim($value, 's');
                                $this->items[$name] = $items[$key+1];
                        }
                }
                $this->contents = $this->db->DBGet($this->items);
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
                        case 'test':
                                echo count($this->content);
                                $json = $this->contents;
                                return json_encode($json);
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
                $str .= '<nav>'.$this->db->DBGet(["title" => "nav"]).'</nav></header>';
                $str .= '<section>'.$this->loadBody().'</section>';
                $str .= '<footer>'.$this->db->DBGet(["title" => "footer"]).'</footer>';
                $str .= '</div></div></body></html>';
                return $str;
        }

        /**
         * @brief
         * Post function handles post requests.
         * @return void Post only generates response code
         */
        private function post()
        {
                if (count($this->post) < 1) return;
                if ($this->post['token'] === NULL) return;
                // Compare hashed user to db user
                $query['Table'] = 'users';
                $query['uname'] = crypt($this->post['user'], getenv("SALT"));
                $id = $this->db->GetItem($query);

                // Get token id pair that matches token in request
                $token = $this->db->DBGetToken($this->post['token']);
                if ($token['user'] !== $id) {
                        http_response_code(403);
                        return "Missing or wrong token";
                }
                $query = NULL;
                $query['title'] = $this->post['title'];
                $query['user'] = $id;
                $query['blob'] = $this->post['blob'];
                $query['auth'] = $this->post['auth'];
                $query['tags'] = $this->post['tags'];
                $dbitem = new DBItem($query);
                if (!$this->db->DBPost($dbitem)) {
                        http_response_code(500);
                        return "Failed the request";
                }
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
                // Get get variables. 
                $vget = explode('?', trim($url, "/"));
                $type = explode('.', $vget[0]);
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
                if ($vget[0] == "") return [];
                $items = explode("/", $vget[0]);
                return $items;
        }
}
?>
