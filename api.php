<?php
    $arr = $_SERVER;
    echo "Request method: " . $arr["REQUEST_METHOD"] . "<br>";
    echo "Path info: " . $arr["PATH_INFO"] . "<br>";
    echo "Request uri: " . $arr["REQUEST_URI"] . "<br>";
    echo "Server signature: " . $arr["SERVER_SIGNATURE"] . "<br>";
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        echo "Recorded GET request!" . "<br>";
    } else {
        echo "api.php: only GET method supported";
    }
?>
