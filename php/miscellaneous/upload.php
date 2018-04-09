<?php
/** upload.php information: 
 * This file is called when information is stored in the database.
 * These should be under db
 */

// insert inserts data into table
function insert($conn, $table, $items) {
	// Construct the query
	$err = "";
	$sql = "INSERT INTO $table (";
	$columns = "";
	$values = "";
	$index = 0;
	$count = count($table) - 1;
	foreach ($items as $column=>$item) {
		$columns .= $column;
		$values .= "'" . $item . "'";
		if ($index == $count) {
			break;
		}
		$columns .= ", ";
		$values .= ", ";
	}
	$sql .= $columns . ") VALUES (" . $values . ");";

	// Query
	if ($conn->query($sql) !== TRUE) {
		$err = "upload.insert: " . $sql . "<br>" . $conn->error;
	}
	return $err;
}

// upload uploads posted data into the database
function upload($database, $table, $items) {
    $err = [];
    
	// Create connection
	$conn = new mysqli($database["Site"], $database["User"], "", $database["Database"]);
	// Check connection
	if ($conn->connect_error) {
		$err[] = "db.upload: Connection failed: " . $conn->connect_error;
		return $err;
	}

    // Create table if it doesn't exists
    if (!checkTable($conn, $table)) {
        $columns = [];
        foreach($items as $key=>$item) {
            $columns[] = $key;
        }
        $err[] = "db.upload: " . createTable($conn, $table, $columns);
	}

	// Insert into or update the table and merge error tables
	//$err[] = insert($conn, $table, $items);
	
	// Close the connection
	$conn->close();
	return $err;
}

// uploaded function informs user about uploaded data
function uploaded() {
    
}
?>