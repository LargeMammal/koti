<?php
/** initialise.php:
 * Initialise database with the editor interface. 
 * I should make it so that this file is only called once
 * and it then uploads the interface to database where it is then 
 * loaded in the future.
 * 
 * Like this file should strait up call upload function and then 
 * reload into upload page. 
 * 
 * Not only should this load the editor, but also basic headers,
 * footers and navigators. Navigator should also read what 
 * categories exist from database. 
 */
function initialise($config, $site, $lang) {
    // I should try to create good looking UI for content editing
    // and managing. Especially managing side. 
    $upload = [
        'title' => 'Initialise',
        'decription' => 'Initialisation page',
        'lang' => 'en-US',
        'content' => '<section>
        <h1>Add content</h1>
        <form action="" method="POST">
            Table: <input type="text" name="table" required><br>
            Title: <input type="text" name="title" required><br>
            Description: <input type="text" name="description" required><br>
            Language: <input type="text" name="lang" required><br>
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

    // Upload editor UI
    $err = upload($database, "upload", $upload);

    // Stuff in head
    $str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead();
    $str .= "</head><body>";

    // Print all errors. If you try to do it else where, 
    // it will break the html structure.
    foreach ($config["err"] as $val) {
        $str .= "Error: " . $val . "<br>";
    }
    $str .= "Upload errors: <br>";
    foreach ($err as $error) {
        $str .= "Error: " . $error . "<br>";
    }

    // Stuff in body
    $str .= loadHeader(["title"=>"Initialise"]);
    $str .= loadBody($upload);
    $str .= loadFooter(["footer"=>"Initialisation site"]);
    $str .= "</body></html>";
    return $str;
}
?>