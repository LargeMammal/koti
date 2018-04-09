<?php
/** initialise.php:
 * initialise database with the editor interface
 */
function initialise($config, $site, $lang) {
    $upload = [
        'title' => 'Initialise',
        'decription' => 'Initialisation page',
        'content' => '<section>
        <h1>Add content</h1>
        <form action="" method="POST">
            Language: <input type="text" name="lang" required><br>
            Table: <input type="text" name="table" required><br>
            Title: <input type="text" name="title" required><br>
            Description: <input type="text" name="description" required><br>
            Content: <textarea name="content" required></textarea><br>
            <input type="submit">
        </form>
        <h1>Add others</h1>
        <form action="" method="POST">
            Navigation: <textarea name="nav" required></textarea><br>
            Lang: <input type="text" name="lang" required><br>
            <input type="submit">
        </form>
        <form action="" method="POST">
            Header: <textarea name="header" required></textarea><br>
            Lang: <input type="text" name="lang" required><br>
            <input type="submit">
        </form>
        <form action="" method="POST">
            Footer: <textarea name="footer" required></textarea><br>
            Lang: <input type="text" name="lang" required><br>
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
    $str .= loadHeader("Initialise");
    $str .= loadNav($nav["data"]);
    $str .= loadBody($upload);
    $str .= loadFooter($footer["data"]);
    $str .= "</body></html>";
    return $str;
}
?>