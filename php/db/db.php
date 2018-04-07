<?php
/** db.php library:
 * db.php should hold functions for handling databases.
 * I'll prolly need to refactor this file in future.
 */

/** On create* functions below:
 * The follofing create* are not really necessary in future.
 * Well that statement is not strictly true. These functions 
 * are hopefully used only once. 
 */

// createTitle creates title table
function createTitle() {
	return "CREATE TABLE '$element' (".
	"title_uri VARCHAR(255) NOT NULL PRIMARY KEY, ".
	"title VARCHAR(255), ".
	"language VARCHAR(64) NOT NULL);";
}

// createTable 
function  createTable($element) {
	$output = "";
	switch ($element) {
		case "title":
			$output = createTitle();
			break;
		case "index":
		default:
			break;
	}
	return $output;
}

// getElement gets specific part of site
function getElement($db, $element, $language) { 
    $output = [
        "err" => "", 
        "result" => [],
    ];
	$conn = new mysqli($db["Site"], $db["User"], "", $db["Database"]);
	if ($conn->connect_error) {
		$output["err"] .= "db.getElement: Connection error: " . $conn->connect_error;
	}

	$sql = "SELECT * FROM information_schema.tables WHERE table_schema = 'site' AND table_name = '$element' LIMIT 1;";

	$result = $conn->query($sql);
	if ($result->num_rows < 1) {
		$output["err"] .= "db.getElement $element not found. Creating $element table...<br>";
		$sql = createElement($element);
		if ($conn->query($sql) === TRUE) {
			$output["err"] .= "Table users created successfully";
		} else {
			$output["err"] .= "Error creating table ". $conn->error;
			return $output;
		}
	}
	$sql = "SELECT * FROM $element;";

	return $output;
}

// checkTable checks if table exists
function checkTable($conn, $table) {
	$result = $conn->query("SHOW TABLES LIKE '$table'");
	if ($result->num_rows < 1) {
		return false;
	}
	return true;
}

// getSite gets specified top site.
function getSite($config, $lang, $site, $title = "") {
	// I should probably turn this into global class
    $output = [
        "err" => [], 
        "data" => [],
    ];
	// Create connection
	$conn = new mysqli($config["Site"], $config["User"], "", $config["Database"]);
	// Check connection
	if ($conn->connect_error) {
		$output["err"][] = "db.getSite: Connection failed: " . $conn->connect_error;
		return $output;
	}

	// If site has multiple values use those for search.
	// For now we'll use only the first
	$main = $site[0];

	// Check if table exists
	if (!checkTable($conn, $main)) {
		$output["err"][] = "db.getSite: Table, $main , not found";
		return $output;
	}
	// Get all stuff with english stuff. Turn language and limit into variables.
	// The way I designed this is that when one wants, for example, the title
	// this returns only that in that language. If you want the contents 
	// this returns all matcing results. User does with them whatever they want.
	$sql = "SELECT * FROM $main WHERE lang='english' LIMIT 10";
	if ($sub != "") {
		$sql = "SELECT * FROM $main WHERE title='$title'";
	}
	// Results
	$results = $conn->query($sql);
	// If none found stop here
	if ($results->num_rows < 1) {
		$outputs["err"][] = "db.getSite: Non found";
		return $output;
	}
	$output["data"] = $results;
	return $output;
}
?>
