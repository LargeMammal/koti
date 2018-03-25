<?php
include "nav.php";

// Load header
function loadHeader($config) {
    $header = "<header>";
    //if (file_exists("css/common.css")) $header .= "File exists!";
    // Get title from db
    $header .= "<h1>Welcome to my corner of the Internet!</h1>";
    $header .= loadNav($config);
    $header .= "</header>";
    return $header;
}
?>