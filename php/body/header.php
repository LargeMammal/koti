<?php
// TODO: Refactor this further

// Load header
function loadHeader() {
    $header = "<header><h1>Welcome to my corner of the Internet!</h1><nav>";
    $header .= "<a href=\"projects/\">Projektit</a>
                <a href=\"https://github.com/LargeMammal\">Github</a>
                <a href=\"https://gitlab.com/mammal\">Gitlab</a>
                <a href=\"https://www.linkedin.com/in/jari-loippo/\">LinkedIn</a>";
    $header .= "</nav></header>";
    return $header;
}
?>
