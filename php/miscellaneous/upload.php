<?php
// Load the whole page 
function loadSaveSite($config, $lang, $site) {
    // Get databases
    $databases = $config["data"];
    // Do we use localhost or other host?
    $content = getSite($databases["Localhost"], $lang, $site);
    foreach($content["err"] as $val) {
        $config["err"][] = $val;
    }

    // Stuff in head
    $str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead($content);
    $str .= "</head><body>";

    // Stuff in body
    $str .= loadHeader($content);

    // create the content.
    $str .= "<section><form method='get'>Stuff<input type='text' name='fname'></input></form></section>";

    $str .= loadFooter($content);
    $str .= "</body></html>";
    return $str;
}
?>