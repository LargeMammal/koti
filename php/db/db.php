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
	return $conn;
}

// checkTable checks if table exists
function checkTable($conn, $items = "") {
	$result = $conn->query("SHOW TABLES LIKE '$items'");
	if ($result->num_rows < 1) {
		return false;
	}
	$result->free();
	return true;
}

// createTitle creates table with given name and data.
// First item in array will become primary key
function createTable($conn, $table, $columns) {
	$sql = "CREATE TABLE ".$table." (";
	$items = [];
	$items[] = "id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY";
	foreach($columns as $column) {
		if ($column == "Title" || $column == "Language") {
			$items[] = "$column VARCHAR(191) NOT NULL";
		} else {
			$items[] = "$column LONGTEXT NOT NULL";
		}
	}
	$sql .= implode(", ", $items);
	$sql .= ")";
	if ($conn->query($sql) !== TRUE) {
		return "db.createTable: ". $sql. ": " . $conn->error;
	}
	return "";
}

/** getItem
* getItem gets an item from database
* Generate the code here and later turn it into a exterrior script
*/
function getItem($database, $lang,  $items, $item = NULL){
	// I should probably turn this into global class
    $output = [
        "err" => [],
        "data" => [],
	];
	$conn = connect($database);
	if (!$conn) {
		$output["err"][] = mysqli_connect_error();
		return $output;
	}

	// If table doesn't exist stop here
	if (!checkTable($conn, $items)) {
		$output["err"][] = "db.getItem: Table, " . $items . " , not found";
		return $output;
	}

	// Generate query
	$sql = "SELECT * FROM " . $items . " WHERE Language='" . $lang . "' ";
	if(!is_null($item)) {
		$sql .= "AND WHERE Title=" . $item . " ";
	}
	$sql .= "LIMIT 10";

	$results = $conn->query($sql);
	// If query fails stop here
	if ($results === FALSE) {
		$output["err"][] = "db.getItem: " . $conn->error;
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
	if (!$conn) {
		$output["err"][] = mysqli_connect_error();
		return $output;
	}

	// Generate query
	$sql = "INSERT INTO ". $table ."(";
	$columns = [];
	$values = [];
	foreach ($items as $column=>$item) {
		$columns[] = $column;
		$values[] = "'" . $item . "'";
	}
	$sql .= implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";

	// If table does exist
	if (!checkTable($conn, $table)) {
		$output["err"][] = "db.getItem: Table, " . $table . " , not found";
		// Create the table
		$error = createTable($conn, $table, $columns);
		// If creation failed table stop here
	    if ($error != "") {
	        $output["err"][] = "db.setItem: " . $error;
			return $output;
	    }
	}

	// Query
	if ($conn->query($sql) !== TRUE) {
		$output["err"][] = "upload.insert: " . $sql . "<br>" . $conn->error;
	} else {
		$output["err"][] = "db.setItem: Upload successfull: " . $sql;
	}
	return $output["err"];
}

// remove selected item
function removeItem($config, $items, $item) {

}
?>
