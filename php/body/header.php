<?php
// LoadHeader returns header field. 
function loadHeader($content) {
    $output = "<header>";
    $output .= "<p>settings icon</p>"; 
    $output .= "<h1>Beware Tardigrades!</h1>";
    $output .= "<p>Everything else, like search and stuff</p>";
    $output .= "</header>";
    return $output;
}
?>