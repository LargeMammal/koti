<?php
// LoadHeader returns header field. 
function loadHeader($title) {
    $output = "<header>"; 
    $output .= "<h1>$title</h1>";
    $output .= "</header>";
    return $output;
}
?>