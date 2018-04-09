<?php
// LoadHeader returns header field. 
function loadHeader($content) {
    $title = $content["title"];
    $output = "<header>";
    $output .= "<p>settings icon</p>"; 
    $output .= "<h1>$title</h1>";
    $output .= "<p>Everything else, like search and stuff</p>";
    $output .= "</header>";
    return $output;
}
?>