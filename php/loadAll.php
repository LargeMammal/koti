<?php
// include necessary functions
include "php/head/title.php";
include "php/body/header.php";
include "php/body/body.php";
include "php/body/footer.php";
// Load the whole page 
function loadAll() {
    $str = "<!DOCTYPE html><html><head>";
    $str .= loadTitle();
    $str .= "</head><body>";
    $str .= loadHeader();
    $str .= loadBody();
    $str .= loadFooter();
    $str .= "</body></html>";
    return $str;
}
?>