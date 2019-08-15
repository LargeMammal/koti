<?php
/** Site.class.php
 * Site class hold data and functions needed to
 * generate a website.
 */
// These too should be merged into one class file
include_once "db/db.php";
include_once "db/initialise.php";

/** Site class
 * More later
 */
class Site {
    private $auth; // 0..3
    private $errors;
    private $lang; // first available language
    private $contents;
    protected $config;
    protected $langs; // all client languages
    protected $items;

    function __construct($config, $langs, $items) {
        $this->config = $config;
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
        $this->errors = NULL;
        $this->langs = NULL;
        $this->config = NULL;
        $this->items = NULL;
    }

    /** Build
     * Generate the site
     */
    public function Build($uid = NULL, $pw = NULL) {
        $this->authorize($uid, $pw);
        $footer = [];

        // You need to fix this
        foreach ($this->langs as $l) {
            $list = explode("-", $l);
            // Reform the language into xx-XX format
            if (count($list) < 2) {
                $list[] = strtoupper($l);
                $l = implode("-", $list);
            }
            $footer = getItem($this->config, ["Table" => "footer"], $l);
            $err = $footer["err"];
            $i = count($err);
            if($i > 0) {
                $this->getErrors($err);
            } else {
                $this->lang = $l;
                $body = getItem($this->config, $this->items, $this->lang);
                foreach ($body['data'] as $key => $value) {
                    // Drop unauthorized stuff
                    if ($this->auth < $value['Auth']) {
                        header('WWW-Authenticate: Basic realm="Tardiland"');
                        header('HTTP/1.0 401 Unauthorized');
                        unset($body['data'][$key]);
                    }
                }
                $this->contents = $body['data'];
                $this->getErrors($body['err']);
                break;
            }
        }

        if (count($footer["data"]) < 1) {
            $err = initLang($this->config);
            $this->getErrors($err);
            $footer["data"][0]["Content"] = 'Initializing';
        }

        // Stuff in head
        $str = '<!DOCTYPE html><html lang="' . $this->lang . '"><head>';
        $str .= $this->loadHead();
        $str .= "</head>";
        // Stuff in body
        $str .= "<body><header>".$this->loadHeader();
        $str .= "<nav>".$this->loadNav()."</nav>"."</header>";
        //* Print all errors. This should be handled by logs
        if (isset($this->errors)) {
            $str .= "<section>";
            foreach($this->errors as $val) {
                if ($val != "") {
                    $str .= $val. "<br>";
                }
            }
            $str .= "</section>";
        }
        //*/
        $str .= "<section>".$this->loadBody()."</section>";
        $str .= "<footer>".$this->loadFooter($footer["data"][0]["Content"]."</footer>");
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
        $auth = getItem($this->config, $query); // Get user data
        $this->getErrors($auth['err']);
        $pwa = "non";
        $autha = 0;
        if (count($auth['data']) > 0) {
            $pwa = $auth['data'][0]["PW"];
            $autha = $auth['data'][0]["Auth"];
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
        $str .= '<title>' . $title . '</title>';
        $str .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $str .= '<link rel="icon" type="image/png" href="/image.png">';
        $str .= '<link rel="stylesheet" type="text/css" href="/css/common.css" >';
        return $str;
    }

    /** loadHeader
     * loadHeader will in future generate custom header
     */
    private function loadHeader() {
        $banner = $this->items['Category'];
        if(isset($this->contents)) {
            if (count($this->contents) == 1) {
                $banner = $this->contents[0]['Title'];
            }
        }
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
        $cats = getItem($this->config, $query, $this->lang);
        // If both outputs are null initialise base functions of the site.
        if (is_null($cats['data'][0])) {
            $this->getErrors(initEditor($this->config));
            // re-do the search
            $cats = getItem($this->config, $query, $this->lang);
        }
        if (isset($cats["err"])) $this->getErrors($cats["err"]);
        // Organize items along categories
        foreach ($cats['data'] as $cat) {
            $list[$cat["Category"]][] = $cat;
        }
        // Generate dropdowns
        $content .= '<ul><a href="/" class="dropdown">home</a>';
        foreach ($list as $key => $value) {
            $content .= '<li class="dropdown">'.
                        '<a href="javascript:void(0)" class="dropbtn">'.$key.'</a>'.
                        '<div class="dropdown-content">';
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
        if (!isset($this->contents)) {
            return "Site came up empty!";

        }
        foreach ($this->contents as $items) {
            $content .= "<section>";
            $content .= "<h2>" . $items['Title'] . "</h2>";
            $content .= $items['Content'];
            $content .= "</section>";
        }
        return $content;
    }

    /** loadFooter
     * loadFooter will in future generate custom footer
     */
    private function loadFooter($footer = "Non-found") {
        return $footer;
    }

    /** getErrors
     * getErrors merges error array into main errors array
     */
    private function getErrors($errs = []) {
        if (count($errs) > 0) {
            foreach($errs as $e) {
                $this->errors[] = $e;
            }
        }
    }
}
?>
