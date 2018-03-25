<?php
// include necessary functions
include "php/head/title.php";
include "php/body/header.php";
include "php/body/body.php";
include "php/body/footer.php";

// Load the whole page 
function loadAll($config) {
    //$item = LoadItem($config["Localhost"]);

    $str = "<!DOCTYPE html><html><head>";
    $str .= loadTitle($config);
    $str .= "</head><body>";
    $str .= loadHeader($config);
    $str .= loadBody($config);
    $str .= loadFooter($config);
    $str .= "</body></html>";
    return $str;
}
?>