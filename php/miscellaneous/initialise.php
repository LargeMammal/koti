<?php
/** initialise.php:
 * initialise database with the editor interface
 */
function initialise($config, $site, $lang) {
    $upload = [
        'title' => 'Initialise',
        'decription' => 'Initialisation page',
        'content' => '<section>
                        <form action="upload" method="POST">
                            Title: <input type="text" name="title"><br>
                            Description: <input type="text" name="description"><br>
                            Content: <textarea name="content"></textarea><br>
                            <input type="submit">
                        </form>
                    </section>',
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
    $str .= loadHeader($content['title']);
    $str .= loadNav($nav["data"]);
    $str .= loadBody($content);
    $str .= loadFooter($footer["data"]);
    $str .= "</body></html>";
    return $str;
}
?>