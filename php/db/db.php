<?php
/** db.php library:
 * db.php should hold functions for handling databases.
 * I'll prolly need to refactor this file in future.
 */

/** On database abstraction:
 * I really shouldn't call "mysqli(something)" 
 * What if I use different db in future?
 * Code needs to be portable and agnostic.
 * Even the create* functions should be
 * like that. Pass table column names as 
 * properties in arrays.
 */

/** On create* functions below:
 * The follofing create* are not really necessary in future.
 * Well that statement is not strictly true. These functions 
 * are hopefully used only once. 
 *     Read the above on how they should be. 
 */

// createTitle creates table with given name and data. 
// First item in array will become primary key
// Use numbered arrays!
function createTable($conn, $table, $columns) {
	$sql = "CREATE TABLE '$table' (";
	$count = count($columns) - 1;
	foreach($columns as $key=>$column) {
		$sql .= "$column VARCHAR(255) NOT NULL";
		if ($key == 0) {
			$sql .= "PRIMARY KEY";
		} else if ($key == $count) {
			break;
		}
		$sql .= ", ";
	}
	$sql .= ");";
	if ($conn->query($sql) !== TRUE) {
		return $conn->error;	
	}
	return "";
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
function getElement($config, $site, $lang = "") {
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
	$element = $site[0];

	// Check if table exists
	if (!checkTable($conn, $element)) {
		$output["err"][] = "db.getSite: Table, $element , not found";
		return $output;
	}
	// Get all stuff with english stuff. Turn language and limit into variables.
	// The way I designed this is that when one wants, for example, the title
	// this returns only that in that language. If you want the contents 
	// this returns all matcing results. User does with them whatever they want.
	$sql = "SELECT * FROM $element WHERE lang=$lang LIMIT 10";
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
?>
