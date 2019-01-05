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
function loadSite($config, $elements, $lang) {
    //$lang = ["en-US"];
    // Get database
    $databases = $config["data"];
    $database = $databases[$databases["Use"]];
    $config["err"][] = "Current site: " . $elements[1];
    // I should make this automatic in case of empty database.
    if ($elements[1] == "initialise") {
        $str = initialise($config, $elements, $lang);
        return $str;
    }

    // get nav element
    foreach ($lang as $key => $value) {
        $nav = getItem($config, "nav", $value);
        if ($nav != "") {
            break;
        }
    }
    $config["err"][] = $nav['err'];

    $content = "";
    // query content based on URI
    foreach($lang as $l) {
        $content = queryContent($config, $elements, $l);
        $data = $content["data"];
        if($content["err"][0] != "") {
            $config["err"][] = $content["err"];
        } else {
            break;
        }
    }
    $data = $content["data"];
    $head = $data[0];
    if (count($data) > 1) {
        $head["title"] = $elements[1];
        $head["description"] = $elements[1] . " top site";
    }

    // get footer element
    $footer = getItem($config, "footer", $lang[0]);
    $config["err"][] = $footer['err'];

    // Stuff in head
    $str = '<!doctype html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead($head);
    $str .= "</head><body>";
    // Stuff in body
    $banner = $elements[1];
    if (count($data) == 1) {
        $banner = ($data[0])['title'];
    }
    $str .= loadHeader($banner);
    $str .= loadNav($config, $nav["data"]);

    //$str .= implode(" ",$elements);
    // Print all errors. 
    foreach($config["err"] as $val) {
        if ($val != "") {
            $str .= "$val <br>";
        }
    }

    $str .= loadBody($data);
    $str .= loadFooter($footer["data"]);
    $str .= "</body></html>";
    return $str;
}
?>
