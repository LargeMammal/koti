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
                if (count($items) < 1) $items = ["content", "home", "Koti"];
                foreach ($rows as $key => $val) {
                        if (!isset($items[$key])) break;
                        if ($key < 3) $this->items[$val] = urldecode($items[$key]);
                        else $this->items["Extras"] = $items[$key];
                }
                //var_dump($this->items);
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
        public function Build($auth = 0, $realm = "Tardiland") 
        {
                $this->auth = $auth; 
                $this->footer = NULL;
                $this->contents = [];

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
                $query = [];
                foreach ($this->items as $key => $value) {
                        if ($key == "Extras") continue;
                        $query[$key] = $value;
                }

                // Go through every language selection
                foreach ($this->langs as $l) {
                        $list = explode("-", $l);
                        if (count($list) < 2) {
                                $list[] = strtoupper($l);
                                $l = implode("-", $list);
                        }
                        $this->contents = $this->db->GetItem($query, $l);
                        if (count($this->contents) < 1) {
                                $this->db->InitEditor();
                                $this->contents = $this->db->GetItem($query, $l);
                        }
                        if (count($this->contents) > 0) {
                                // Drop unauthorized stuff
                                foreach ($this->contents as $key => $value) {
                                        $auth = 0;
                                        if (isset($value["Auth"])) 
                                                $auth = $value["Auth"];
                                        else $auth = 3;
                                        if ($this->auth < $auth) {
                                                header("WWW-Authenticate: Basic realm='$realm'"); 
                                                http_response_code(401);
                                        }
                                        if ($this->auth < $auth) 
                                                unset($this->contents[$key]);
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

        /** 
         * loadHead function should generate the head elements.
         * Later I should add meta handling
         */
        private function loadHead() 
        {
                $title = $this->items['Table']; // TODO: Undefined index: Category
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
                        return "<h1>".$this->items['Category']."</h1>"; 
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
                $specifics = ['Title', 'Category'];
                $list = [];
                $content = "";
                $cats = $this->db->GetItem($query, $this->lang, $specifics);
                if (count($cats) < 1) return $content;
                // Organize items along categories
                foreach ($cats as $cat) $list[$cat["Category"]][] = $cat;
                //$list = $cats;

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
                                        $cat["Category"].'/'.
                                        $cat["Title"].'">'.
                                        $cat['Title'].'</a>';
                        }
                        $content .= '</div></li>';
                }
                $content .= '<a href="https://github.com/LargeMammal" class="dropdown">github</a>';
                $content .= '<a href="https://gitlab.com/mammal" class="dropdown">gitlab</a>';
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
}
?>
