<?php
// Load meta data
include_once "site/head.php";
// Load elements functions
include_once "site/header.php";
include_once "site/nav.php";
include_once "site/body.php";
include_once "site/footer.php";
// Depending on database call specific library
include_once "db/db.php";
// Load initialise functions just in case
include_once "db/initialise.php";

// Load the whole page
function loadSite($config, $langs, $items, $item = NULL) {
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
    $langs[] = "fi-FI"; // Add default language

    //* Get items
    foreach ($langs as $l) {
        $err = [];
        $list = explode("-", $l);
        // Reform the language into fi-FI format
        if (count($list) < 2) {
            $list[] = strtoupper($l);
            $l = implode("-", $list);
        }
        $lang = $l;
        $config["err"][] = "loadSite.loadSite: Loading ".$lang;
        $nav = getItem($database, $l, "nav");
        $content = getItem($database, $l, $items, $item);
        $footer = getItem($database, $l, "footer");
        $err = $nav["err"];
        foreach ($footer["err"] as $e) $err[] = $e;
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
        $err = initLang($database);
        foreach ($err as $e) {
            $config["err"][] = $e;
        }
        $nav["data"][0]["Content"] = 'Initializing';
        $footer["data"][0]["Content"] = 'Initializing';
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
    $str = '<!DOCTYPE html><html lang="' . $lang . '"><head>';
    $str .= loadHead($head);
    $str .= "</head><body>";
    // Stuff in body
    $str .= loadHeader($banner);
    $str .= "<nav>" . $nav["data"][0]["Content"] . "</nav>";
    // html apparently wants heading for sections.
    $str .= "<section>";
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
    $str .= loadFooter($footer["data"][0]["Content"]);
    $str .= "</body></html>";
    return $str;
}
?>
