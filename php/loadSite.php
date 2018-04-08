<?php
// Load meta data 
include_once "php/head/head.php";
// Load site functions
include_once "php/body/header.php";
include_once "php/body/nav.php";
include_once "php/body/body.php";
include_once "php/body/footer.php";
// Load database functions
include_once "php/db/db.php";
// Load initialise function just in case
include_once "php/miscellaneous/initialise.php";

// Load the whole page 
function loadSite($config, $site, $lang) {
    if ($site[0] == "initialise") {
        return initialise($config, $site, $lang);
    }
    // Get database
    $database = ($config["data"])["Localhost"];
    // Get elements and errors
    $head = getElement($database, ["head"], $lang);
    foreach($head["err"] as $val) {
        $config["err"][] = $val;
    }
    $nav = getElement($database, ["nav"], $lang);
    foreach($nav["err"] as $val) {
        $config["err"][] = $val;
    }
    $content = getElement($database, $site);
    foreach($content["err"] as $val) {
        $config["err"][] = $val;
    }
    $footer = getElement($database, ["footer"], $lang);
    foreach($footer["err"] as $val) {
        $config["err"][] = $val;
    }

    // Stuff in head
    $str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead($head["data"]);
    $str .= "</head><body>";

    // Print all errors. If you try to do it else where, it will break the html structure.
    foreach($config["err"] as $val) {
        $str .= "Error: " . $val . "<br>";
    }

    // Stuff in body
    $str .= loadHeader($content["data"]);
    $str .= loadNav($nav["data"]);
    $str .= loadBody($content["data"]);
    $str .= loadFooter($footer["data"]);
    $str .= "</body></html>";
    return $str;
}
?>