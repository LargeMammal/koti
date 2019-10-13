<?php
/** Site class
 * More later
 */
class Site 
{
        private $auth; // 0..3
        private $categories;
        private $contents;
        private $db;
        private $footer;
        private $errors;
        private $items;
        private $lang; // first available language
        private $langs; // all client languages

        function __construct($db, $langs, $items) 
        {
                $this->db = $db;
                $this->langs = $langs;
                if (count($this->langs) < 1) 
                        $this->langs[] = "fi-FI"; // default language
                $this->items = [];
                $rows = ["Table", "Category", "Title", "Extras"];
                if (count($items) < 1) $items = ["content", "home"];
                foreach ($rows as $key => $val) {
                        if (!isset($items[$key])) break;
                        if ($key < 3) $this->items[$val] = $items[$key];
                        else $this->items["Extras"] = $items[$key];
                }
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

        /** Build
         * Generate the site
         */
        public function Build($uid = NULL, $pw = NULL) 
        {
                $this->auth = 0; // Purge previous authentication
                $this->authorize($uid, $pw);
                $this->footer = NULL;
                $this->contents = [];

                $footers = $this->db->GetItem(["Table" => "footer"]);
                if (count($footers) < 1) {
                        $this->db->InitFooter();
                        $footers = $this->db->GetItem(["Table" => "footer"]);
                }
                // TODO: check for fitting footer in language list
                foreach ($this->langs as $lang) {
                        foreach ($footers as $footer) {
                                if ($lang == $footer['Language']) continue;
                                $this->footer = $footer["Content"];
                                break;
                        }
                        if (is_null($this->footer)) continue;
                        break;
                }

                foreach ($this->langs as $l) {
                        // Go through every language selection
                        $list = explode("-", $l);
                        if (count($list) < 2) {
                                $list[] = strtoupper($l);
                                $l = implode("-", $list);
                        }

                        $this->contents = $this->db->GetItem($this->items, $l);
                        if (count($this->contents) < 1) {
                                // Drop unauthorized stuff
                                foreach ($this->contents as $key => $value) {
                                        if ($this->auth >= $value['Auth']) 
                                                continue;
                                        header('WWW-Authenticate: 
                                                Basic realm="Tardiland"');
                                        http_response_code(401);
                                        unset($contents[$key]);
                                        die("Unauthorized");
                                }
                                if (count($this->contents) < 1) 
                                        $this->contents = NULL;
                                $this->lang = $l;
                                break;
                        }
                }

                // Stuff in head
                $str = '<!DOCTYPE html><html lang="'.$this->lang.'"><head>';
                $str .= $this->loadHead();
                $str .= "</head>";
                // Stuff in body
                $str .= "<body><header>".$this->loadHeader();
                $str .= "<nav>".$this->loadNav()."</nav></header>";
                $str .= "<section>".$this->loadBody()."</section>";
                $str .= "<footer>".$this->footer."</footer>";
                $str .= "</body></html>";
                return $str;
        }

        /** authorize
         * authorize queries db for user name and password hash.
         * It then compares the two and returns authorization.
         */
        private function authorize($uid = NULL, $pw = NULL) 
        {
                $query = [
                        "Table" => "users",
                        "UID" => $uid,
                ];
                $auth = $this->db->GetItem($query); // Get user data
                $pwa = "non";
                $autha = 0;
                if (count($auth) > 0) {
                        $pwa = $auth[0]["PW"];
                        $autha = $auth[0]["Auth"];
                }
                if (!password_verify($pw, $pwa)) 
                        $this->auth = $autha;
        }

        /** 
         * loadHead function should generate the head elements.
         * Later I should add meta handling
         */
        private function loadHead() 
        {
                $title = $this->items['Category']; // TODO: Undefined index: Category
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
                if (!isset($this->contents)) 
                        return "<h1>".$this->items['Category']."</h1>"; // TODO: Undefined index: Category
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
                $query = [ 'Table' => 'content' ];
                $list = [];
                $content = "";
                $cats = $this->db->GetItem($query, $this->lang);
                // If both outputs are null initialise database
                if (is_null($cats)) {
                        $this->db->InitEditor();
                        // re-do the search
                        $cats = $this->db->GetItem($query, $this->lang);
                }
                // Organize items along categories
                foreach ($cats as $cat) $list[$cat["Category"]][] = $cat;

                // Generate dropdowns
                $content .= '<ul><a href="/" class="dropdown">home</a>';
                foreach ($list as $key => $value) {
                        $content .= "<li class='dropdown'>
                                        <a href='javascript:void(0)' 
                                        class='dropbtn'>$key</a>
                                        <div class='dropdown-content'>";
                        foreach ($value as $cat) {
                                $content .= '<a href="/'.
                                        $cat["Category"].'/'.
                                        $cat["Title"].'">'.
                                        $cat['Title'].'</a>';
                        }
                        $content .= '</div></li>';
                }
                $content .= "<ul>";
                return $content;
        }

        /** loadBody
         * loadBody will generate content section of the page
         */
        private function loadBody() 
        {
                $content = "";
                if (count($this->contents) < 1) // TODO: count(): Parameter must be an array or an object that implements Countable
                        return "<h1>Site came up empty!</h1>";
                if ($this->items['Table'] === 'errors') {
                        $content .= "<section><table>";
                        $rows = [];
                        $columns = [];
                        foreach ($this->contents as $key => $val) {
                                $rows[] = $key;
                                $columns[] = $val;
                        }
                        $content .= '<tr>';
                        foreach ($rows as $val) $content .= "<th>$val</th>";
                        $content .= '</tr>';
                        foreach ($this->contents as $item) {
                                $content .= '<tr>';
                                foreach ($item as $val) 
                                        $content .= "<td>$item</td>";
                                $content .= '</tr>';
                        }
                        $content .= "</table></section>";
                } else {
                        foreach ($this->contents as $items) {
                                $content .= "<section>";
                                $content .= "<h2>" . $items['Title'] . "</h2>";
                                $content .= $items['Content'];
                                $content .= "</section>";
                        }
                }
                return $content;
        }
}
?>
