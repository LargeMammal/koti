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

    // get head element
    $head = getElement($conn, "head", $lang[0]);
    $config["err"][] = $head['err'];

    // get nav element
    $nav = getElement($conn, "nav", $lang[0]);
    $config["err"][] = $nav['err'];
    
    // query content based on URI
    $content = queryContent($conn, $elements);
    $config["err"][] = $content["err"];
    
    // get footer element
    $footer = getElement($conn, "footer", $lang[0]);
    $config["err"][] = $footer['err'];

    // Stuff in head
    $str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead($head["data"]);
    $str .= "</head><body>";

    $banner = $elements[0];
    if (count($content) === 1) {
        $banner = ($content[0])['title'];
    }

    // Stuff in body
    $str .= loadHeader($banner);
    $str .= loadNav($conn, $nav["data"]);

    // Print all errors. If you try to do it else where, it will break the html structure.
    foreach($config["err"] as $val) {
        if ($val != "") {
            $str .= "$val <br>";
        }
    }

    $str .= loadBody($content["data"]);
    $str .= loadFooter($footer["data"]);
    $str .= "</body></html>";
    return $str;
    $conn->close();
}
?>