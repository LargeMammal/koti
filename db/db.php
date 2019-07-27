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
function connect($database) {
	// Create connection
	$conn = new \mysqli($database["Site"], $database["User"], $database["Pass"], $database["Database"]);
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
	// All this should be given in an array
	foreach($columns as $column) {
		if ($column == "Title" || $column == "Language" || $column == "PW" || $column == "UID") {
			$items[] = "$column VARCHAR(255) NOT NULL";
		} elseif ($column == "Auth" || $column == "Verified") {
			$items[] = "$column TINYINT NOT NULL";
		} elseif ($column == "Date") {
			$items[] = "$column BIGINT NOT NULL";
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
function getItem($database, $inputs, $lang = NULL){
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

	//* Sanitize input
	$items = [];
	foreach ($inputs as $key => $value) {
		$items[$conn->escape_string($key)] = $conn->escape_string($value);
	}
	//*/

	// If table doesn't exist stop here
	if (!checkTable($conn, $items["Table"])) {
		$output["err"][] = "db.getItem: Table, ".$items["Table"]." , not found";
		return $output;
	}

	// Generate query
	$sql = "SELECT * FROM ".$items["Table"];
	$str = "";
	if (isset($lang)) {
	 	$str = " WHERE Language='" . $lang . "'";
	}
	foreach ($items as $column=>$item) {
		if ($column != "Table") {
			if ($str != "") $str .= " AND";
			else $str .= " WHERE";
			$str .= " ".$column."='".$item."'";
		}
	}
	$sql .= $str." LIMIT 10";

	$results = $conn->query($sql);
	// If query fails stop here
	if ($results === FALSE) {
		$output["err"][] = "db.getItem: ".$sql."; ".$conn->error;
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
function setItem($config, $table, $inputs) {
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

	//* Sanitize inputs
	$items = [];
	foreach ($inputs as $key => $value) {
		$items[$conn->escape_string($key)] = $conn->escape_string($value);
	}
	//*/

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
		$output["err"][] = "db.setItem: Table, " . $table . " , not found";
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
		$output["err"][] = "db.setItem: " . $sql . "<br>" . $conn->error;
	}
	return $output["err"];
}

// remove selected item
function removeItem($config, $items, $item) {

}
?>
