<?php
/** upload.php information: 
 * This file is called when information is stored in the database.
 * These should be under db
 */

// insert inserts data into table
function insert($conn, $table, $items) {
    $sql = "INSERT INTO '$table'";
}

// upload uploads posted data into the database
function upload($table, $items) {
    $err = [];
    
	// Create connection
	$conn = new mysqli($config["Site"], $config["User"], "", $config["Database"]);
	// Check connection
	if ($conn->connect_error) {
		$output["err"][] = "db.getSite: Connection failed: " . $conn->connect_error;
		return $output;
	}

    // Create table if it doesn't exists
    if (!checkTable($conn, $table)) {
        $columns = [];
        foreach($items as $key=>$item) {
            $columns[] = $key;
        }
        $err[] = createTable($conn, $table, $columns);
    }

	// Insert into or update the table.
	$sql = "INSERT * FROM $element WHERE lang=$lang LIMIT 10";
	if ($lang === "") {
		$title = $site[1];
		$sql = "SELECT * FROM $element WHERE title='$title'";
	}
	// Results
	$results = $conn->query($sql);
	// If none found stop here
	if ($results->num_rows < 1) {
		$outputs["err"][] = "db.getSite: Non found";
		return $output;
	}
	$output["data"][] = $results;
	return $output;
}

// uploaded function informs user about uploaded data
function uploaded() {
    
}

// Load the whole page 
function loadSaveSite($config, $site, $lang) {
    // Get databases
    $databases = $config["data"];
    // Do we use localhost or other host?
    $content = getSite($databases["Localhost"], $lang, $site);
    foreach($content["err"] as $val) {
        $config["err"][] = $val;
    }

    // Stuff in head
    $str = '<!DOCTYPE html><html lang="' . $lang[0] . '"><head>';
    $str .= loadHead($content);
    $str .= "</head><body>";

    // Stuff in body
    $str .= loadHeader($content);

    // create the content.
    $str .= "<section><form method='get'>Stuff<input type='text' name='fname'></input></form></section>";

    $str .= loadFooter($content);
    $str .= "</body></html>";
    return $str;
}
?>