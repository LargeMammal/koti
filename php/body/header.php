<?php
include "nav.php";
// TODO: Refactor this further
// TODO: Split this into header and nav

// Load header
function loadHeader() {
    $header = "<header>";
    // Get title from db
    $header .= "<h1>Welcome to my corner of the Internet!</h1>";
    $header .= loadNav();
    $header .= "</header>";
    return $header;
}
?>