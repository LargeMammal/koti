<?php 
// TODO: This definintely needs more refactoring
// loadBody parses contents of 
function loadBody($content) {
    // html apparently wants heading for sections.
    //$output = "<section>";
    foreach ($content as $items) {
        $output .= "<section>";
        $output .= "<h2>" . $items['title'] . "</h2>";
        $output .= "<h5>" . $items['description'] . "</h5>";
        $output .= $items['content'];
        $output .= "</section>";
    }
    //$output .= "</section>"; 
    return $output;
}
?>