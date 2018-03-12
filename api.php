
<!DOCTYPE html>
<html>
<?php
    $arr = $_SERVER;
    echo "Request method: " . $arr["REQUEST_METHOD"] . "<br>";
    echo "Path info: " . $arr["PATH_INFO"] . "<br>";
    echo "Request uri: " . $arr["REQUEST_URI"] . "<br>";
    echo "Server signature: " . $arr["SERVER_SIGNATURE"] . "<br>";
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Get values from URI
        $str = $_SERVER["PATH_INFO"];
        $arr = explode("/", $str); // Explode path into variables
        // Count the variables
        $len = sizeof($arr);
        // Split into multiple paths
        switch ($len) {
            case 1: // Present the base site
                // Refactor the sites into functions.
                break;
            case 2: // Present site with this name
                // somesite()
                break;
            default: // The default case: just show the main site or something
                // mainpage()
        }
        // Do something with those variables
        echo "Recorded GET request!" . "<br>";
    } else {
        echo "api.php: only GET method supported";
    }
?>
</html>