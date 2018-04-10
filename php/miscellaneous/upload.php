<?php
/** upload.php information: 
 * This file is called when information is stored in the database.
 * These should be under db
 */

// insert inserts data into table
function insert($conn, $table, $items) {
	// Construct the query
	$err = NULL;
	$sql = "INSERT INTO $table (";
	$columns = [];
	$values = [];
	foreach ($items as $column=>$item) {
		$columns[] = $column;
		$values[] = "'" . $item . "'";
	}
	$sql .= implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";

	// Query
	if ($conn->query($sql) !== TRUE) {
		$err = "upload.insert: " . $sql . "<br>" . $conn->error;
	}
	return $err;
}

// upload uploads posted data into the database
function upload($config, $table, $items) {
	// Get database
	$database = ($config["data"])["Localhost"];
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
		$error = createTable($conn, $table, $columns);
		if ($error != "") {
			$err[] = "db.upload: " . $error;
		}
	}

	// Insert into or update the table.
	$error = insert($conn, $table, $items);
	if (isset($error)) {
		$err[] = "db.upload: " . $error;
	} else {
		$err[] = "db.upload: Upload was successfull!";
	}
	// Close the connection
	$conn->close();
	return $err;
}
?>