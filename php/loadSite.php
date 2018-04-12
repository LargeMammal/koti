<?php
// Load meta data 
include_once "php/head/head.php";
// Load elements functions
include_once "php/body/header.php";
include_once "php/body/nav.php";
include_once "php/body/body.php";
include_once "php/body/footer.php";
// Load database functions
include_once "php/db/db.php";
// Load initialise functions just in case
include_once "php/miscellaneous/initialise.php";

// Load the whole page 
function loadSite($config, $elements, $lang) {
    $lang = ["en-US"];
    // Get database
    $database = ($config["data"])["Localhost"];
    // I should make this automatic in case of empty database.
    if ($elements[0] == "initialise") {
        return initialise($config, $elements, $lang);
    }

    // Connect to database
    $conn = connect($database);
    if (is_string($conn)) {
        return "Could not connect to database: " . $conn;
    }

    // get nav element
    $nav = getElement($conn, "nav", $lang[0]);
    $config["err"][] = $nav['err'];
    
    $content = "";
    // query content based on URI
    foreach($lang as $l) {
        $content = queryContent($conn, $elements, $l);
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
        $head["title"] = $elements[0];
        $head["description"] = $elements[0] . " top site";
    }
    
    // get footer element
    $footer = getElement($conn, "footer", $lang[0]);
    $config["err"][] = $footer['err'];

    // Stuff in head
    $str = ' <!doctype html><html lang="' . $lang[0] . '"><head>';
    //$str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead($head);
    $str .= "</head><body>";
    // Stuff in body
    $banner = $elements[0];
    if (count($data) == 1) {
        $banner = ($data[0])['title'];
    }
    $str .= loadHeader($banner);
    $str .= loadNav($conn, $nav["data"]);

    // Print all errors. If you try to do it else where, it will break the html structure.
    foreach($config["err"] as $val) {
        if ($val != "") {
            $str .= "$val <br>";
        }
    }

    $str .= loadBody($data);
    $str .= loadFooter($footer["data"]);
    $str .= "</body></html>";
    return $str;
    $conn->close();
}
?>