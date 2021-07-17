<?php
/** 
 * Server.class.php holds Server class
 * Server object will handle http methods
 */

class Server {
        private $categories;
        private $contents;
        private $db;
        private $errors;
        private $footer;
        private $form; // html/json/xml
        private $get;
        private $items;
        private $post;
        private $pw;
        private $uid;
        private $method;

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
                $this->form = "html";
                $this->db = new DB($this->config);
                $this->server = $this->paths($server['REQUEST_URI']);
                $this->post = $post;
                $this->get = $get;
                $this->items = [];
                $this->startTime = $time;
                $timer = round(microtime(true) * 1000); // Start benchmark
                $this->method = $server['REQUEST_METHOD'];
                $this->site = new Site($this->db, $server, $get, $post);
        }
        
        function __destruct() 
        {
                $this->auth = NULL;
                $this->categories = NULL;
                $this->contents = NULL;
                $this->db = NULL;
                $this->footer = NULL;
                $this->errors = NULL;
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
                return $output;
        }
        
        /**
         * @brief
         * Get function handles get requests. Adds necessary headers.
         * More accurately it formats the request. Request is done in 
         * construction. 
         * @return string Get function returns site in string form
         */
        public function Get()
        {
                $items = $this->server;
                
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
                // Search ends
                $this->footer = NULL;

                switch ($this->form) {
                        case 'json':
                                header('Content-Type: application/json');
                                return json_encode($this->contents);
                        case 'xml':
                                /**
                                 * TODO: Fix this
                                 */
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

                $footers = $this->db->DBGet(["title" => "footer"]);

                // Stuff in head
                $str = '<!DOCTYPE html><head>';
                $str .= $this->loadHead();
                $str .= '</head>';
                // Stuff in body
                $str .= '<body><div id="root"><div><header>'.$this->loadHeader();
                $str .= '<nav>'.$this->loadNav().'</nav></header>';
                $str .= '<section>'.$this->loadBody().'</section>';
                $str .= '<footer>'.$this->footer.'</footer>';
                $str .= '</div></div></body></html>';
                return $str;
        }

        /**
         * @brief
         * Post function handles post requests.
         * @return void Post only generates response code
         */
        public function Post()
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
        public function Delete()
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

        /** loadNav
         * loadNav loads nav bar. 
         */
        private function loadNav()
        {
                // Get all data from content table
                $list = [];
                $content = "";
                $cats = $this->db->DBGet(["title" => "footer"]);
                if (count($cats) < 1) return $content;
                // Organize items along categories
                foreach ($cats as $cat) $list[$cat["Main"]][] = $cat;

                // Generate dropdowns
                $content .= '<ul>';
                foreach ($list as $key => $value) {
                        $content .= "<li class='dropdown'>
                                <a href='javascript:void(0)' 
                                class='dropbtn'>$key</a>
                                <div class='dropdown-content'>";
                        foreach ($value as $cat) {
                                $content .= '<a href="/'.
                                        'content/Titles/'.
                                        $cat["Title"].'">'.
                                        $cat['Title'].'</a>';
                        }
                        $content .= '</div></li>';
                }
                $content .= '<a href="https://github.com/LargeMammal" ';
                $content .= 'class="dropdown">github</a>';
                $content .= '<a href="https://gitlab.com/mammal" ';
                $content .= 'class="dropdown">gitlab</a>';
                $content .= "<ul>";
                return $content;
        }

        /** loadBody
         * loadBody will generate content section of the page
         */
        private function loadBody() 
        {
                $content = "";
                if (is_null($this->contents) || count($this->contents) < 1) 
                        return "<h1>Site came up empty!</h1>";
                if ($this->items['Table'] === 'errors') {
                        $content .= "<section><table>";
                        $rows = [];
                        foreach ($this->contents[0] as $key => $val) {
                                $rows[] = $key;
                        }
                        $content .= '<tr>';
                        foreach ($rows as $val) $content .= "<th>$val</th>";
                        $content .= '</tr>';
                        foreach ($this->contents as $item) {
                                $content .= '<tr>';
                                foreach ($item as $key => $val) {
                                        if ($key == "Time")
                                                $content .= "<td>".date("H:i:s",$val)."</td>";
                                        else $content .= "<td>$val</td>";
                                }
                                $content .= '</tr>';
                        }
                        $content .= "</table></section>";
                } else {
                        foreach ($this->contents as $items) {
                                $content .= "<section>";
                                $content .= $items['Content'];
                                $content .= "</section>";
                        }
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
                $form = explode('.', $vget[0]);
                if (count($form) > 1) {
                        switch ($form[count($form)-1]) {
                        case 'json':
                                $this->form = 'json';
                                unset($form[count($form)-1]);
                                $vget[0] = implode('.', $form);
                                break;
                        case 'xml':
                                $this->form = 'xml';
                                unset($form[count($form)-1]);
                                $vget[0] = implode('.', $form);
                                break;
                        case 'test':
                                $this->form = 'test';
                                unset($form[count($form)-1]);
                                $vget[0] = implode('.', $form);
                                break;
                        default:
                                throw new Exception("form not recognized");
                                break;
                        }
                }
                if ($vget[0] == "") return [];
                $items = explode("/", $vget[0]);
                return $items;
        }
}
?>
