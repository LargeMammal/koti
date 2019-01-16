<?php
// Load meta data
include_once "php/head/head.php";
// Load elements functions
include_once "php/body/header.php";
include_once "php/body/nav.php";
include_once "php/body/body.php";
include_once "php/body/footer.php";
// Depending on database call specific library
include_once "php/db/mysql.php";
// Load initialise functions just in case
include_once "php/miscellaneous/initialise.php";

// Load the whole page
function loadSite($config, $langs, $items, $item) {
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
    $head = [];
    $nav = [];
    $footer = [];
    $lang = "";

    // Get items
    foreach ($langs as $key => $l) {
        $err = [];
        $lang = $l;
        $nav = getItem($database, $l, "nav");
        $content = getItem($database, $l, $items, $item);
        $footer = getItem($database, $l, "footer");
        $err[] = $nav["err"];
        $config["err"] = $content["err"];
        $err[] = $footer["err"];
        $data = $content["data"];
        if(count($err[]) > 0) {
            foreach ($err as $e) {
                $config["err"][] = $e;
            }
        } else {
            break;
        }
    }

    $head["title"] = $items;
    $banner = $items;
    if (count($data) > 1) {
        $head["description"] = $item . " top site";
    } else {
        $banner = ($data[0])['title'];
    }

    // Stuff in head
    $str = '<!doctype html><html lang="' . $lang . '"><head>';
    $str .= loadHead($head);
    $str .= "</head><body>";
    // Stuff in body
    $str .= loadHeader($banner);
    $str .= loadNav($nav["data"]);
    // html apparently wants heading for sections.
    $str = "<section><section>";
    // Print all errors.
    foreach($config["err"] as $val) {
        if ($val != "") {
            $str .= "$val <br>";
        }
    }
    $str .= "</section>";
    $str .= loadBody($data);
    $str .= "</section>";
    $str .= loadFooter($footer["data"]);
    $str .= "</body></html>";
    return $str;
}
?>
