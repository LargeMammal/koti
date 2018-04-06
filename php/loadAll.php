<?php
// include necessary functions
include_once "php/head/head.php";
include_once "php/body/header.php";
include_once "php/body/body.php";
include_once "php/body/footer.php";
include_once "php/db/db.php";

// Load the whole page 
function loadAll($config, $lang, $site) {
    // Get databases
    $databases = $config["data"];
    // Do we use localhost or other host?
    $content = getSite($databases["Localhost"], $lang, $site);
    foreach($content["err"] as $val) {
        $config["err"][] = $val;
    }

    // Stuff in head
    $str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead($content);
    $str .= "</head><body>";

    // If you want to test something do it here. Only prints if error occurs.
    // If you try to do it else where, it will break the html structure.
    foreach($config["err"] as $val) {
        $str .= "Error: " . $val . "<br>";
    }

    // Stuff in body
    $str .= loadHeader($content);
    $str .= loadBody($content);
    $str .= loadFooter($content);
    $str .= "</body></html>";
    return $str;
}
?>