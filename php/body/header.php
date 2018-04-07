<?php
include "nav.php";

// Load header
function loadHeader($content) {
    $header = "<header>";
    //if (file_exists("css/common.css")) $header .= "File exists!";
    // Get title from db
    $header .= "<h1>Welcome to my corner of the Internet!</h1>";
    $header .= loadNav($content);
    $header .= "</header>";
    return $header;
}
?>