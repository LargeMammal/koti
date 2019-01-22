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
    private $auth;
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
        $this->items = $items;
    }
    function __destruct() {
        $this->auth = NULL;
        $this->config = NULL;
        $this->langs = NULL;
        $this->items = NULL;
    }

    function __toString() {
        $nav = [];
        $footer = [];

        foreach ($this->langs as $l) {
            $list = explode("-", $l);
            // Reform the language into xx-XX format
            if (count($list) < 2) {
                $list[] = strtoupper($l);
                $l = implode("-", $list);
            }
            $nav = getItem($this->config, $l, "nav");
            $footer = getItem($this->config, $l, "footer");
            $err = $nav["err"];
            foreach ($footer["err"] as $e) $err[] = $e;
            $i = count($err);
            if($i > 0) {
                $this->getErrors($err);
            } else {
                $body = getItem($this->config, $l, $this->items[0], $this->items[1]);
                $this->contents = $body['data'];
                $this->getErrors($body['err']);
                $this->lang = $l;
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

    /** loadHead
     * This function should generate the head elements.
     * Later I should add meta handling
     */
    private function loadHead() {
        $title = $this->items[0];
        $description = $this->items[0] . " | top site";
        if (count($this->contents) == 1) {
            $title = $this->contents[0]['Title'];
            $description = $this->contents[0]['Title'] . " | top site";
        }
        $str = '<meta charset="UTF-8">';
        $str .= '<title>' . $title . '</title>';
        $str .= '<meta name="description" content="' . $description . '">';
        $str .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $str .= '<link rel="stylesheet" type="text/css" href="css/common.css" >';
        return $str;
    }

    // loadHeader will in future generate custom header
    private function loadHeader() {
        $banner = $this->items[0];
        if (count($this->contents) == 1) {
            $banner = $this->contents[0]['Title'];
        }
        $output = "<h1>$banner</h1>";
        return $output;
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

    // LoadNav loads nav bar. I should use nav as settings bar like in google apps.
    private function loadNav($content = 'Non-found'){
        return $content;
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
