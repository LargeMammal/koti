<?php
include_once 'php/db/db.php';
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
function initialise($config) {
    // I should try to create good looking UI for content editing
    // and managing. Especially managing side.
    $upload = [
        'Title' => 'Upload',
        'Description' => 'Upload page',
        'Language' => 'en-US',
        'Content' => '<section>
        <h1>Add content</h1>
        <form action="" method="POST">
            <p>Table: </p><input type="text" name="table" required><br>
            <p>Title: </p><input type="text" name="title" required><br>
            <p>Description: </p><input type="text" name="description" required><br>
            <p>Language: </p><input type="text" name="lang" required><br>
            <p>Content: </p><textarea name="content" required></textarea><br>
            <input type="submit">
        </form>
        <h1>Add Language</h1>
        <form action="" method="POST">
            <p>Lang: </p><input type="text" name="lang" required><br>
            <p>Navigation: </p><textarea name="nav" required></textarea><br>
            <p>Footer: </p><textarea name="footer" required></textarea><br>
            <input type="submit">
        </form>
    </section>',
    ];

    // Upload editor UI
    $err = setItem($config, "upload", $upload);

    // Stuff in head
    $str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead("Initialise");
    $str .= "</head><body>";
    $str .= loadHeader("Initialise");

    // Print all errors. If you try to do it else where,
    // it will break the html structure.
    foreach ($err as $error) {
        if ($error != "") {
            $str .= "Error: " . $error . "<br>";
        }
    }

    // Stuff in body
    $str .= "<p>Move to ";
    $str .= "https://tardigrade.ddns.net/api/upload ";
    $str .= "to start uploading</p>";
    $str .= loadFooter("Initialisation site");
    $str .= "</body></html>";
    return $str;
}

function initLang($config, $lang = "en-US", $footer_text='Made by me with PHP and trying to follow REST standard') {
   // I should probably turn this into global class
   $output = [
       "err" => [],
       "data" => [],
   ];
    // I should try to create good looking UI for content editing
    // and managing. Especially managing side.
    $nav = [
        'Language' => $lang,
        'Content' => '<a href="https://github.com/LargeMammal">Github</a><a href="https://gitlab.com/mammal">Gitlab</a><a href="https://www.linkedin.com/in/jari-loippo/">LinkedIn</a>',
    ];
    $footer = [
        'Language' => $lang,
        'Content' => $footer_text,
    ];

    // Upload language
    $err = setItem($config, "nav", $nav);
    foreach ($err as $e) $output["err"][] = "initialise.initLang".$e;
    $err = setItem($config, "footer", $footer);
    foreach ($err as $e) $output["err"][] = "initialise.initLang".$e;
    return $output;
}
?>