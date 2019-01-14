<?php
// TODO: Refactor this further

// Load header
function loadHeader() {
    $header = "<header><h1>Welcome to my corner of the Internet!</h1><nav>";
    $header .= "<a href=\"projects/\">Projektit</a>
                <a href=\"https://github.com/K1729\">Github</a>
                <a href=\"https://gitlab.com/K1729\">Gitlab</a>
                <a href=\"https://www.linkedin.com/in/jari-loippo-272331115/\">LinkedIn</a>";
    $header .= "</nav></header>";
    return $header;
}
?>
