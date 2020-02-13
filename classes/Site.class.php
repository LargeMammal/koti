<?php
/** Site class
 * More later
 */
class Site 
{
        private $categories;
        private $contents;
        private $db;
        private $errors;
        private $footer;
        private $form; // html/json/xml
        private $get;
        private $items;
        private $lang; // first available language
        private $langs; // all client languages
        private $post;
        private $pw;
        private $uid;

        function __construct($db, $server, $post = NULL) 
        {
                $this->auth = 0; // Make sure to purge privileges
                $this->contents = [];
                $this->form = "html";
                $this->realm = "vesikarhu.fi";
                $this->db = $db;
                $this->server = $server;
                $this->post = $post;

                // Search start
                /**
                 * TODO: 
                 * 1. Query should returns array of matching results
                 * * first field is table. If only table is filled 
                 *      return all items in table.
                 * * second is Title. /table/title. If title field is
                 *      is filled then search items with that title. 
                 * 2. Results then are parsed according to client data
                 * 3. Use get variables in parsing
                 * 4. Move init stuff to DB side.
                 */
                $items = $this->paths($server['REQUEST_URI']);
                
                $this->langs = ["fi-FI"];
                if (isset($server['HTTP_ACCEPT_LANGUAGE'])){
                        $lang =$this->getLang($server['HTTP_ACCEPT_LANGUAGE']);
                        $this->langs = $lang;
                }
                /**
                 * Running a test. Dunno if http authentication resends the
                 * request or will the code restart where it was called. 
                 */
                $this->pw = NULL;
                $this->uid = NULL;
                if (isset($server['PHP_AUTH_USER']) && 
                        isset($server['PHP_AUTH_PW'])) {
                        $this->pw = $server['PHP_AUTH_PW'];
                        $this->uid = $server['PHP_AUTH_USER'];
                }
                // This gets user authorization from db 
                $this->authorize();

                $this->items = [];
                if (count($items) < 1) $items = ["content", "Title", "Koti"];
                $this->items["Table"] = $items[0];
                array_shift($items);
                foreach ($items as $key => $value) {
                        if ($key % 2 == 0) {
                                $name = trim($value, 's');
                                $this->items[$name] = $items[$key+1];
                        }
                }
                $this->contents = $this->db->GetItem($this->items);
                // The rest should be moved to db side 
                if (count($this->contents) < 1) {
                        $this->db->InitEditor();
                        $this->contents =$this->db->GetItem($this->items);
                }
                if (count($this->contents) < 1) 
                        $this->contents = NULL;
                // Search ends
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
                $this->lang = NULL;
                $this->langs = NULL;
        }

        /**
         * Get function handles get requests.
         */
        public function Get()
        {
                if($this->items["Table"] == "users") return;
                $this->footer = NULL;

                // Drop unauthorized stuff
                foreach ($this->contents as $key => $value) {
                        $auth = 0;
                        if (isset($value["Auth"])) 
                                $auth = $value["Auth"];
                        else $auth = 3;

                        // TODO: This should only be called if 
                        // authentication is needed. 
                        if ($this->auth < $auth) {
                                header("WWW-Authenticate: " .
                                        "Basic realm='$this->realm'");
                                http_response_code(401);
                        }
                        if ($this->auth < $auth) 
                                unset($this->contents[$key]);
                }

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
                                //header('Content-Type: application/json');
                                echo count($this->content);
                                $json = $this->contents;
                                return json_encode($json);
                        default:
                                # code...
                                break;
                }

                $footers = $this->db->GetItem(["Table" => "footer"]);
                if (count($footers) < 1) {
                        $this->db->InitFooter();
                        $footers = $this->db->GetItem(["Table" => "footer"]);
                }
                // check for fitting footer in language list
                foreach ($this->langs as $lang) {
                        $list = explode("-", $lang);
                        if (count($list) < 2) {
                                $list[] = strtoupper($lang);
                                $lang = implode("-", $list);
                        }
                        foreach ($footers as $footer) {
                                if ($lang != $footer['Language']) continue;
                                $this->footer = $footer["Content"];
                                break;
                        }
                        if (is_null($this->footer)) continue;
                        break;
                }

                // Stuff in head
                $str = '<!DOCTYPE html><html lang="'.$this->lang.'"><head>';
                $str .= $this->loadHead();
                $str .= '</head>';
                // Stuff in body
                $str .= '<body><div id="root"><header>'.$this->loadHeader();
                $str .= '<nav>'.$this->loadNav().'</nav></header>';
                $str .= '<section>'.$this->loadBody().'</section>';
                $str .= '<footer>'.$this->footer.'</footer>';
                $str .= '</body></html>';
                return $str;
        }

        /**
         * Post function handles post requests.
         */
        public function Post()
        {
                if (count($this->post) < 1) return;
                $this->post["Date"] = time();

                /**
                 * TODO:
                 * 1. Get table structure from db
                 * * DESCRIBE table; gives you table columns 
                 *   and explanations
                 */
                $fields = $this->db->GetTableFields($this->items["Table"]);
                
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
                $query = ['Table' => 'content'];
                $specifics = ['Title', 'Main'];
                $list = [];
                $content = "";
                $cats = $this->db->GetItem($query, $this->lang, $specifics);
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
                                        $this->items["Table"].'/'.
                                        $cat["Main"].'/'.
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
         * paths extracts file path and get variables from URI
         */
        private function paths($url) {
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
                if (count($vget) > 1)
                        $this->get = explode('&', $vget[1]);
                if ($vget[0] == "") return [];
                $items = explode("/", $vget[0]);
                return $items;
        }
}
?>
