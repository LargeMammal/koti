<?php
/** initialise.php:
 * initialise database with the editor interface
 */
function initialise($config, $site, $lang) {
    $upload = [
        'title' => 'Initialise',
        'decription' => 'Initialisation page',
        'content' => include("php/miscellaneous/initialise.html"),
    ];

    // Get database
    $database = ($config["data"])["Localhost"];
    // Get elements and errors
    $head = getElement($database, ["head"], $lang);
    foreach($head["err"] as $val) {
        $config["err"][] = $val;
    }
    $nav = getElement($database, ["nav"], $lang);
    foreach($nav["err"] as $val) {
        $config["err"][] = $val;
    }
    $content = include("php/miscellaneous/initialise.html");
    $footer = getElement($database, ["footer"], $lang);
    foreach($footer["err"] as $val) {
        $config["err"][] = $val;
    }

    // Stuff in head
    $str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead($head["data"]);
    $str .= "</head><body>";

    // Print all errors. If you try to do it else where, 
    // it will break the html structure.
    foreach($config["err"] as $val) {
        $str .= "Error: " . $val . "<br>";
    }

    // Stuff in body
    $str .= loadHeader($content);
    $str .= loadNav($nav["data"]);
    $str .= loadBody($content);
    $str .= loadFooter($footer["data"]);
    $str .= "</body></html>";
    return $str;
}
?>