<?php 
// LoadNav loads nav bar. I should use nav as settings bar like in google apps.
function loadNav($content) {
    $nav = "<nav>";
    // Get links from $content.
    $nav .= "<a href=\"main/hobbies.html\">Harrastukset</a>";
    $nav .= "<a href=\"main/programs.html\">Ohjelmat</a>";
    $nav .= "<a href=\"projects/\">Projektit</a>";
    $nav .= "<a href=\"https://github.com/K1729\">Github</a>";
    $nav .= "<a href=\"https://gitlab.com/K1729\">Gitlab</a>";
    $nav .= "<a href=\"https://www.linkedin.com/in/jari-loippo-272331115/\">LinkedIn</a>";
    // Remove hardcoded links
    $nav .= "</nav>";
    return $nav;
}
?>