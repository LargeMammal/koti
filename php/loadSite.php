<?php
// Load meta data
include_once "php/head/head.php";
// Load elements functions
include_once "php/body/header.php";
include_once "php/body/nav.php";
include_once "php/body/body.php";
include_once "php/body/footer.php";
// Depending on database call specific library
include_once "php/db/db.php";
// Load initialise functions just in case
include_once "php/server/initialise.php";

// Load the whole page
function loadSite($config, $langs, $items, $item = "") {
    // Get database
    $databases = $config["data"];
    $database = $databases[$databases["Use"]];
    $config["err"][] = "Current site: " . $items;
    // I should make this automatic in case of empty database.
    if ($items == "initialise") {
        $str = initialise($database, $items, $langs);
        return $str;
    }
    $data = [];
    $nav = [];
    $footer = [];
    $lang = "";

    //* Get items
    foreach ($langs as $l) {
        $err = [];
        $lang = $l;
        $nav = getItem($database, $l, "nav");
        $content = getItem($database, $l, $items, $item);
        $footer = getItem($database, $l, "footer");
        $err = $nav["err"];
        foreach ($footer as $e) $err[] = implode($e);
        $data = $content;
        if(count($err) > 0) {
            foreach ($err as $e) {
                $config["err"][] = $e;
            }
        } else {
            break;
        }
    }
    foreach ($data["err"] as $value) {
        $config["err"][] = $value;
    }

    if (!isset($nav["data"][0]["Content"])) {
        initLang($database);
        $nav["data"][0]["Content"] = '<a href="https://github.com/LargeMammal">Github</a><a href="https://gitlab.com/mammal">Gitlab</a><a href="https://www.linkedin.com/in/jari-loippo/">LinkedIn</a>';
        $footer["data"][0]["Content"] = 'Made by me with PHP and trying to follow REST standard';
    }

    //*/
    $head["Title"] = $items;
    $banner = $items;
    if (count($data) > 1) {
        $head["Description"] = $item . " top site";
    } else {
        if (isset($data)) $banner = $data["data"][0]['Title'];
    }

    // Stuff in head
    $str = '<!doctype html><html lang="' . $lang . '"><head>';
    $str .= loadHead($head);
    $str .= "</head><body>";
    // Stuff in body
    $str .= loadHeader($banner);
    $str .= loadNav($nav["data"][0]["Content"]);
    // html apparently wants heading for sections.
    $str = "<section><section>";
    //file_put_contents("error.log", $config["err"]);
    //* Print all errors.
    foreach($config["err"] as $val) {
        if ($val != "") {
            $str .= $val. "<br>";
        }
    }
    //*/
    $str .= "</section>";
    $str .= loadBody($data["data"]);
    $str .= "</section>";
    $str .= loadFooter($footer["data"][0]["Content"]);
    $str .= "</body></html>";
    return $str;
}
?>
