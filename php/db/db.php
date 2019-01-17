<?php
/** db.php library:
 * db.php should hold functions for handling databases.
 * I'll prolly need to refactor this file in future.
 */

/** On database abstraction:
 * I really shouldn't call "mysqli(something)"
 * What if I use different db in future?
 * Code needs to be portable and agnostic.
 * In future I may need to create a seperate
 * program to handle databases.
 */

 // This is here just to abstract the database layer
function connect($database) {
	// Create connection
	$conn = new mysqli($database["Site"], $database["User"], $database["Pass"], $database["Database"]);
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
	// If table doesn't exist stop here
	if (!checkTable($conn, $items)) {
		return "db.getItem: Table, " . $items . " , not found";
	}
	$sql = "CREATE TABLE ".$table." (";
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
function getItem($database, $lang,  $items, $item = ''){
	// I should probably turn this into global class
    $output = [
        "err" => [],
        "data" => [],
	];
	$conn = connect($database);

	// If table doesn't exist stop here
	if (!checkTable($conn, $items)) {
		$output["err"][] = "db.getItem: Table, " . $items . " , not found";
		return $output;
	}

	// Generate query
	$sql = "SELECT * FROM " . $items . " WHERE lang='" . $lang . "' ";
	if($item != "") {
		$sql .= "AND WHERE title=" . $item . " ";
	}
	$sql .= "LIMIT 10";

	$results = $conn->query($sql);
	// If query fails stop here
	if ($results === FALSE) {
		$output["err"][] = "db.getItem: " . $conn->error;
		$results->free();
		return $output;
	}

	// If none found stop here
	if ($results->num_rows < 1) {
		$output["err"][] = "db.getItem: Non found";
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
//*/

// insert inserts data into table
// Those using setItem should have special privileges
function setItem($config, $table, $items) {
   // I should probably turn this into global class
   $output = [
       "err" => [],
       "data" => [],
   ];

    // Connect
    $conn = connect($config);

	$sql = "INSERT INTO". $table ."(";
	$columns = [];
	$values = [];
	foreach ($items as $column=>$item) {
		$columns[] = $column;
		$values[] = "'" . $item . "'";
	}
    $error = createTable($conn, $table, $columns);
    if ($error != "") {
        $output["err"][] = "db.setItem: " . $error;
    }
	$sql .= implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";

	// Query
	if ($conn->query($sql) !== TRUE) {
		$output["err"][] = "upload.insert: " . $sql . "<br>" . $conn->error;
	}
	return $output["err"];
}

// remove selected item
function removeItem($config, $table, $item) {

}
?>
