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

 // This is here just to abstract the database layer
function connect($config) {
	// Create connection
	$conn = new mysqli($config["Site"], $config["User"], $config["Pass"], $config["Database"]);
	// Check connection
	if ($conn->connect_error) {
		return "db.connect: " . $conn->connect_error;
	}
	return $conn;
}

// checkTable checks if table exists
function checkTable($conn, $table) {
	$result = $conn->query("SHOW TABLES LIKE '$table'");
	if ($result->num_rows < 1) {
		return false;
	}
	$result->free();
	return true;
}

// createTitle creates table with given name and data.
// First item in array will become primary key
function createTable($conn, $table, $columns) {
	$sql = "CREATE TABLE $table (";
	$count = count($columns) - 1;
	$items = [];
	$items[] = "id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY";
	foreach($columns as $column) {
		if ($column == "title" || $column == "lang") {
			$items[] = "$column VARCHAR(191) NOT NULL";
		} else {
			$items[] = "$column LONGTEXT NOT NULL";
		}
	}
	$sql .= implode(", ", $items);
	$sql .= ")";
	if ($conn->query($sql) !== TRUE) {
		return "db.createTable: $sql " . $conn->error;
	}
	return "";
}

/** getItem
* getItem gets an item from database
* Generate the code here and later turn it into a exterrior script
*/
function getItem($config, $lang,  $items, $item = "") {
	// I should probably turn this into global class
    $output = [
        "err" => [],
        "data" => [],
	];

	// Check if table exists
	if (!checkTable($conn, $items)) {
		$output["err"][] = "db.queryContent: Table, " . $items . " , not found";
		return $output;
	}

	$sql = "SELECT * FROM " . $items . " WHERE lang='" . $lang . "' ";
	if($item != "") {
		$sql .= "AND WHERE title=" . $item . " ";
	}
	$sql .= "LIMIT 10";

	$results = $conn->query($sql);
	// Results
	if ($results === FALSE) {
		$output["err"][] = "db.queryContent: " . $conn->error;
		return $output;
	}

	// If none found stop here
	if ($results->num_rows < 1) {
		$output["err"][] = "db.queryContent: Non found";
		$results->free();
		return $output;
	}

	// Fetch each row in associative form and pass it to output.
	while($row = $results->fetch_assoc()) {
		$output["data"][] = $row;
	}
	$results->free();
	return $output;
}
?>
