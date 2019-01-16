<?php
/** mysql.php library:
* mysql.php holds mysql related functions. abstracted so that db:s can be interchanged easily.
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
    // Check if table exists
    if (!checkTable($conn, $table)) {
        return "db.queryContent: Table, " . $table . " , not found";
    }
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

// queryAll gets all items in said category.
function getItem($config, $elements, $lang) {
    // I should probably turn this into global class
    $output = [
        "err" => [],
        "data" => [],
    ];

    // Connect
    $conn = connect($config);

    // Check if table exists
    if (!checkTable($conn, $elements[1])) {
        $output["err"][] = "db.queryContent: Table, " . $elements[0] . " , not found";
        return $output;
    }

    // Generate sql query
    $sql = "SELECT * FROM " . $elements[1] . " ";
    if(count($elements) > 2) {
        $sql .= "WHERE title=" . $elements[2] . " ";
    } else {
        $sql .= "WHERE lang='" . $lang . "' ";
    }
    $sql .= "LIMIT 10";

    // Query
    $results = $conn->query($sql);
    // Check if query was a success
    if ($results === FALSE) {
        $output["err"][] = "db.queryContent: " . $conn->error;
        return $output;
    }

    // If none found, report it
    if ($results->num_rows < 1) {
        $output["err"][] = "db.queryContent: Non found";
    }

    // Fetch each row in associative form and pass it to output.
    while($row = $results->fetch_assoc()) {
        $output["data"][] = $row;
    }
    $results->free();
    $conn->close();
    return $output;
}

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

	$sql = "INSERT INTO $table (";
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
