<?php
// loadBody parses contents of
function loadBody($contents) {
    /* $content .= '<video playsinline autoplay muted loop poster="https://carboncostume.com/wordpress/wp-content/uploads/2015/11/hackerman.jpg" id="bgvid">';
    $content .= '<source src="hackerman_hacktime.mp4" type="video/mp4">';
    $content .= '</video>'; */
    //$content .= '<iframe id="bgvid" src="https://www.youtube.com/embed/KEkrWRHCDQU?autoplay=1" frameborder="0" allow="autoplay; encrypted-media"></iframe>';
    $content = "<section>";
    foreach ($contents as $items) {
        $content .= "<section>";
        $content .= "<h2>" . $items['Title'] . "</h2>";
        $content .= $items['Content'];
        $content .= "</section>";
    }
    if ($content == "") {
        $content .= "Site came up empty!";
    }
    $content .= "</section>";
    return $content;
}
?>
