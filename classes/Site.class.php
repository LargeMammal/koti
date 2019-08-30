<?php
/** Site.class.php
 * Site class hold data and functions needed to
 * generate a website.
 */

/** Site class
 * More later
 */
class Site {
    private $auth; // 0..3
    private $categories;
    private $contents;
    private $db;
    private $footer;
    private $errors;
    private $items;
    private $lang; // first available language
    private $langs; // all client languages

    function __construct($db, $langs, $items) {
        $this->db = $db;
        $this->langs = $langs;
        if (count($this->langs) < 1) $this->langs[] = "fi-FI"; // Add the default language
        $this->items = [
            "Table" => 'content',
            "Category" => urldecode($items[0]),
    	];
        if (isset($items[1])) $this->items["Title"] = urldecode($items[1]);
    }

    function __destruct() {
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
    public function Build($uid = NULL, $pw = NULL) {
        $this->auth = 0; // Purge previous authentication
        $this->authorize($uid, $pw);
        $this->footer = NULL;
        $this->contents = NULL;

        foreach ($this->langs as $l) {
            // Go through every language selection
            $list = explode("-", $l);
            if (count($list) < 2) {
                $list[] = strtoupper($l);
                $l = implode("-", $list);
            }

            if (is_null($this->footer)) {
                $footer = $this->db->GetItem(["Table" => "footer"], $l);
                // Skip empty
                if (count($footer) > 0) 
                    $this->footer = $footer[0]["Content"];
            }
            $this->contents = $this->db->GetItem($this->items, $l);
            if (count($this->contents) < 1) {
                // Drop unauthorized stuff
                foreach ($this->contents as $key => $value) {
                    if ($this->auth < $value['Auth']) {
                        header('WWW-Authenticate: Basic realm="Tardiland"');
                        header('HTTP/1.0 401 Unauthorized');
                        unset($contents[$key]);
                        die("Unauthorized");
                    }
                }
                if (count($this->contents) < 1) $this->contents = NULL;
                $this->lang = $l;
                break;
            }
        }

        //*
        if (is_null($this->footer)) {
            //$this->db->initLang();
            $this->footer = 'Initializing';
        }
        //*/

        // Stuff in head
        $str = '<!DOCTYPE html><html lang="' . $this->lang . '"><head>';
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
    private function authorize($uid = NULL, $pw = NULL) {
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
        if (!password_verify($pw, $pwa)) {
            $this->auth = $autha;
        }
    }

    /** loadHead
     * This function should generate the head elements.
     * Later I should add meta handling
     */
    private function loadHead() {
        $title = $this->items['Category'];
        if (isset($this->contents)) {
            if (count($this->contents) == 1) {
                $title = $this->contents[0]['Title'];
            }
        }
        $str = '<meta charset="UTF-8">';
        $str .= "<title>$title</title>";
        $str .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $str .= '<link rel="icon" type="image/png" href="/image.png">';
        $str .= '<link rel="stylesheet" type="text/css" href="/css/common.css" >';
        return $str;
    }

    /** loadHeader
     * loadHeader will in future generate custom header
     */
    private function loadHeader() {
        $banner = "";
        if (!isset($this->contents)) return "<h1>".$this->items['Category']."</h1>";
        if (count($this->contents) == 1) $banner = $this->contents[0]['Title'];
        $output = "<h1>$banner</h1>";
        return $output;
    }

    /** loadNav
     * loadNav loads nav bar. I should use nav as settings bar like in google apps.
     */
    private function loadNav(){
        // Get all data from content table
        $query = [ 'Table' => 'content' ];
        $list = [];
        $content = "";
        $cats = $this->db->GetItem($query, $this->lang);
        // If both outputs are null initialise base functions of the site.
        if (is_null($cats)) {
            $this->db->initEditor();
            // re-do the search
            $cats = $this->db->GetItem($query, $this->lang);
        }
        // Organize items along categories
        foreach ($cats as $cat) $list[$cat["Category"]][] = $cat;

        // Generate dropdowns
        $content .= '<ul><a href="/" class="dropdown">home</a>';
        foreach ($list as $key => $value) {
            $content .= "<li class='dropdown'>
                        <a href='javascript:void(0)' class='dropbtn'>$key</a>
                        <div class='dropdown-content'>";
            foreach ($value as $cat) {
                $content .= '<a href="/'.$cat["Category"].'/'.$cat["Title"].'">'.$cat['Title'].'</a>';
            }
            $content .= '</div></li>';
        }
        $content .= "<ul>";
        return $content;
    }

    /** loadBody
     * loadBody will generate content section of the page
     */
    private function loadBody() {
        $content = "";
        if (!isset($this->contents)) return "<h1>Site came up empty!</h1>";
        foreach ($this->contents as $items) {
            $content .= "<section>";
            $content .= "<h2>" . $items['Title'] . "</h2>";
            $content .= $items['Content'];
            $content .= "</section>";
        }
        return $content;
    }
}
?>
