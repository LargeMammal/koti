<?php 
// TODO: This definintely needs more refactoring
// loadBody parses contents of 
function loadBody($content) {
    // html apparently wants heading for sections.
    $output = "<section>";
    /* $output .= '<video playsinline autoplay muted loop poster="https://carboncostume.com/wordpress/wp-content/uploads/2015/11/hackerman.jpg" id="bgvid">';
    $output .= '<source src="hackerman_hacktime.mp4" type="video/mp4">';
    $output .= '</video>'; */
    //$output .= '<iframe id="bgvid" src="https://www.youtube.com/embed/KEkrWRHCDQU?autoplay=1" frameborder="0" allow="autoplay; encrypted-media"></iframe>';
    foreach ($content as $items) {
        $output .= "<section>";
        $output .= "<h2>" . $items['title'] . "</h2>";
        $output .= "<h5>" . $items['description'] . "</h5>";
        $output .= $items['content'];
        $output .= "</section>";
    }
    $output .= "</section>"; 
    return $output;
}
?>