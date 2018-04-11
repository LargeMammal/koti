<?php 
// TODO: This definintely needs more refactoring
// loadBody parses contents of 
function loadBody($content) {
    $output = "<section>";
    foreach ($content as $items) {
        $output .= "<section>";
        $output .= "<h1>" . $items['title'] . "</h1>";
        $output .= "<h5>" . $items['description'] . "</h5>";
        $output .= $items['content'];
        $output .= "</section>";
    }
    $output .= "</section>"; 
    return $output;
}
?>