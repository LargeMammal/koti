<?php
/** upload.php information: 
 * This file is called when information is stored in the database.
 */
// upload uploads posted data into the database
function upload($element, $items) {

}

// uploaded function informs user about uploaded data
function uploaded() {
    
}

// Load the whole page 
function loadSaveSite($config, $site, $lang) {
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