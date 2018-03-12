<?php
    // This isn't necessary anymore
    echo "Request method: " . $_SERVER["REQUEST_METHOD"] . "<br>";
    echo "Path info: " . $_SERVER["PATH_INFO"] . "<br>";
    echo "Request uri: " . $_SERVER["REQUEST_URI"] . "<br>";
    echo "Server signature: " . $_SERVER["SERVER_SIGNATURE"] . "<br>";

    // Study about cookies and how to load the language from header
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        echo "api.php: Recorded GET request!" . "<br>";
        // Get values from URI
        $str = $_SERVER["PATH_INFO"];
        $arr = explode("/", $str); // Explode path into variables
        // Count the variables
        $len = sizeof($arr);
        // Split into multiple paths
        switch ($len) {
            case 1: // Present the base site
                // Refactor the sites into functions
                break;
            case 2: // Load site with two variables
                // somesite()
                break;
            default: // The default case: just load the main site.
                include "index.php";
        }
        // Do something with those variables
    } else {
        echo "api.php: only GET method supported";
    }
?>