<?php
function checkUser($user) {
    $username = "nm24";
    $password = "298c8e5d4ab9d6b7e84bce83836e3e33";
    $servername = "localhost";
    $dbname = "nordicmedia24";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE username = '" . $user . "'"; // Noitten on pakko olla tuossa.
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['blogger'] == 0 && $row['editor'] == 0) {
                echo "unauthorized";
                die("unauthorized");
            }
        }
    }
}
// Give a string or number that represents a language and 
// return an array that holds the language 
function loadLanguage($language) {

    return $output;
}
?>