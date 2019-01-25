<?php
include_once 'db/db.php';
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

function initReg($uid, $pw, $name, $mail) {
    // I should probably turn this into global class
    $output = [
        "err" => [],
        "data" => [],
    ];
    $users = [
        'UID' => $uid,
        'PW' => $pw,
        'Mail' => $mail,
        'Name' => $name,
        'Date' => time(),
        'Auth' => 0,
        'Verified' => 0,
    ];
    $err = setItem($config, "users", $users);
    foreach ($err as $e) $output["err"][] = "initialise.initLogin: ".$e;
    return $output["err"];
}

function initEditor($config) {
   // I should probably turn this into global class
   $output = [
       "err" => [],
       "data" => [],
   ];
   // A quick editor
   $editor = [
        'Title' => 'Editor',
        'Content' => '<h1>Add content</h1>
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
        </form>',
        'Category' => 'Käyttäjä',
        'Language' => 'fi-FI',
        'Auth' => 2,
        'Date' => time(),
    ];
    // A quick editor
    $register = [
        'Title' => 'Editor',
        'Content' => '<h1>Add content</h1>
        <form action="" method="POST">
            <p>Table: </p><input type="text" name="table" required><br>
            <p>Title: </p><input type="text" name="title" required><br>
            <p>Description: </p><input type="text" name="description" required><br>
            <p>Language: </p><input type="text" name="lang" required><br>
            <p>Content: </p><textarea name="content" required></textarea><br>
            <input type="submit">
        </form>',
        'Category' => 'Käyttäjä',
        'Language' => 'fi-FI',
        'Auth' => 2,
        'Date' => time(),
    ];

    // Upload editor UI
    $err = setItem($config, "Content", $editor);
    foreach ($err as $e) $output["err"][] = "initialise.initLang: ".$e;
    $err = setItem($config, "Content", $register);
    foreach ($err as $e) $output["err"][] = "initialise.initLang: ".$e;
    return $output["err"];
}

function initLang($config, $lang = "fi-FI", $footer_text='Tein nämä sivut PHP:llä, yrittäen noudattaa REST mallia') {
   // I should probably turn this into global class
   $output = [
       "err" => [],
       "data" => [],
   ];
    $footer = [
        'Language' => $lang,
        'Content' => $footer_text,
    ];

    $err = setItem($config, "footer", $footer);
    foreach ($err as $e) $output["err"][] = "initialise.initLang: ".$e;
    return $output["err"];
}
?>
