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
        $this->config = $config[$config["Use"]];
        $this->langs = $langs;
        $this->langs[] = "fi-FI"; // Add the default language
        $this->items = [
            "Table" => 'Content',
            "Category" => $items[0],
            "Title" => $items[1],
    	];
    }
    function __destruct() {
        $this->auth = NULL;
        $this->config = NULL;
        $this->langs = NULL;
        $this->items = NULL;
    }

    function Build($uid = NULL, $pw = NULL) { // vaihda buildiks joka haluaa uid ja pw
        authenticate($uid, $pw);
        $nav = [];
        $footer = [];

        // You need to fix this
        foreach ($this->langs as $l) {
            $list = explode("-", $l);
            // Reform the language into xx-XX format
            if (count($list) < 2) {
                $list[] = strtoupper($l);
                $l = implode("-", $list);
            }
            $nav = getItem($this->config, $this->items, $l);
            $footer = getItem($this->config, $this->items, $l);
            $err = $nav["err"];
            foreach ($footer["err"] as $e) $err[] = $e;
            $i = count($err);
            if($i > 0) {
                $this->getErrors($err);
            } else {
                $this->lang = $l;
                $body = getItem($this->config, $this->items, $this->lang);
                foreach ($body['data'] as $value) {
                    if ($auth < $value['Auth']) {
                        header('WWW-Authenticate: Basic realm="My Realm"');
                        header('HTTP/1.0 401 Unauthorized');
                        return "Unauthorized user!";
                    }
                }
                $this->contents = $body['data'];
                $this->getErrors($body['err']);
                break;
            }
        }

        if (!isset($nav["data"][0]["Content"])) {
            $err = initLang($database);
            foreach ($err as $e) {
                $this->errors[] = $e;
            }
            $nav["data"][0]["Content"] = 'Initializing';
            $footer["data"][0]["Content"] = 'Initializing';
        }

        // Stuff in head
        $str = '<!DOCTYPE html><html lang="' . $this->lang . '"><head>';
        $str .= $this->loadHead();
        $str .= "</head>";
        // Stuff in body
        $str .= "<body><header>".$this->loadHeader()."</header>";
        $str .= "<nav>".$this->loadNav($nav["data"][0]["Content"])."</nav>";
        $str .= "<section>";
        //* Print all errors. This should be handled by logs
        foreach($this->errors as $val) {
            if ($val != "") {
                $str .= $val. "<br>";
            }
        }
        //*/
        $str .= "</section>";
        $str .= "<section>".$this->loadBody()."</section>";
        $str .= "<footer>".$this->loadFooter($footer["data"][0]["Content"]."</footer>");
        $str .= "</body></html>";
        return $str;
    }

    private function authenticate($uid = NULL, $pw = NULL) {
        $query = [
            "Table" => "users",
            "User" => $uid,
        ];
        $query = getItem($this->config, $query);
        $this->auth;
    }

    /** loadHead
     * This function should generate the head elements.
     * Later I should add meta handling
     */
    private function loadHead() {
        $title = $this->items['Title'];
        if (count($this->contents) == 1) {
            $title = $this->contents[0]['Title'];
        }
        $str = '<meta charset="UTF-8">';
        $str .= '<title>' . $title . '</title>';
        $str .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $str .= '<link rel="stylesheet" type="text/css" href="css/common.css" >';
        return $str;
    }

    // loadHeader will in future generate custom header
    private function loadHeader() {
        $banner = $this->items['Table'];
        if (count($this->contents) == 1) {
            $banner = $this->contents[0]['Title'];
        }
        $output = "<h1>$banner</h1>";
        return $output;
    }

    // LoadNav loads nav bar. I should use nav as settings bar like in google apps.
    private function loadNav($content = 'Non-found'){
        // Get all data from content table
        $query = [
            'Table' => 'Content',
            'Title' => NULL,
        ];
        $list = [];
        $content = "";
        $cats = getItem($this->config, $query, $this->lang);
        // If both outputs are null initialise base functions of the site.
        if (is_null($cats['data']) && is_null($cats['err'])) {
            $err = initEditor($config);
            // re-do the search
            $cats = getItem($this->config, $query, $this->lang);
        }
        // Extract categories from data. Not suitable for large databases.
        // Either fix in code or split databases along languages
        foreach ($cats['data'] as $cat) {
            $item = $cat['Category'];
            $no = 0;
            foreach ($list as $value) {
                if ($item == $value) {
                    $no = 1;
                }
            }
            if ($no === 0) {
                $list[] = $item;
            }
        }
        return $content;
    }

    // loadBody will generate content section of the page
    private function loadBody() {
        $content = "";
        foreach ($this->contents as $items) {
            $content .= "<section>";
            $content .= "<h2>" . $items['Title'] . "</h2>";
            $content .= $items['Content'];
            $content .= "</section>";
        }
        if ($content == "") {
            $content .= "Site came up empty!";
        }
        return $content;
    }

    // loadFooter will in future generate custom footer
    private function loadFooter($footer = "Non-found") {
        return $footer;
    }

    // getErrors merges error array into main errors array
    private function getErrors($errs = []) {
        if (count($errs) > 0) {
            foreach($errs as $e) {
                $this->errors[] = $e;
            }
        }
    }
}
?>
