<?php 
// TODO: This definintely needs more refactoring
// loadBody parses contents of 
function loadBody($content) {
    $output = "<section>";
    $output .= "<p>Content items: " . count($content) . "</p>";
    foreach ($content as $items) {
        $output .= "<section>";
        $output .= "<h1>" . $items['title'] . "</h1>";
        $output .= "<h2>" . $items['description'] . "</h2>";
        $output .= $items['content'];
        $output .= "</section>";
    }
    $output .= "</section>"; 
    return $output;
}
?>