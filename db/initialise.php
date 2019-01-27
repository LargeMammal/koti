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
        'Title' => 'Editori',
        'Content' => '<h1>Lisää </h1>
        <form action="content" method="POST">
            <p><input type="text" name="Title" placeholder="Title for the content" required></p><br>
            <p><textarea name="Content" placeholder="Content in html form" required></textarea></p><br>
            <p><input type="text" name="Language" placeholder="Language in xx-XX form" required></p><br>
            <p>Required level of authorization(0min and 3max): <input type="number" name="auth" min="0" max="3" required></p><br>
            <input type="submit">
        </form>
        <h1>Add Language</h1>
        <form action="footer" method="POST">
            <p><textarea name="Content" placeholder="Text in footer" required></textarea></p><br>
            <p><input type="text" name="Language" placeholder="Language in xx-XX form" required></p><br>
            <input type="submit">
        </form>',
        'Category' => 'content',
        'Language' => 'fi-FI',
        'Auth' => 2,
        'Date' => time(),
    ];
    // A quick editor
    $register = [
        'Title' => 'Rekisteröidy',
        'Content' => '<h1>Rekisteröidy</h1>
        <form action="user" method="POST">
            <p><input type="text" name="uid" placeholder="Username" required></p><br>
            <p><input type="password" name="pw" placeholder="Password" required></p><br>
            <p><input type="email" name="email" placeholder="Email" required></p><br>
            <input type="submit">
        </form>',
        'Category' => 'user',
        'Language' => 'fi-FI',
        'Auth' => 0,
        'Date' => time(),
    ];

    // Upload editor UI
    $err = setItem($config, "content", $editor);
    foreach ($err as $e) $output["err"][] = "initialise.initLang: ".$e;
    $err = setItem($config, "content", $register);
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
