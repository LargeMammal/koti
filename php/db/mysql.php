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

// checkTable checks if table exists
function checkTable($conn, $table) {
   $result = $conn->query("SHOW TABLES LIKE '$table'");
   if ($result->num_rows < 1) {
       return false;
   }
   $result->free();
   return true;
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

   $sql = "SELECT * FROM " . $elements[1] . " ";
   if(count($elements) > 2) {
       $sql .= "WHERE title=" . $elements[2] . " ";
   } else {
       $sql .= "WHERE lang='" . $lang . "' ";
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
       if (!isset($elements[0])) {
           $sql = "SELECT * FROM " . $elements[0];
           $sql .= " WHERE lang='en-US'";
           $sql .= " LIMIT 10";
           $results = $conn->query($sql);
           // Results
           if ($results === FALSE) {
               $output["err"][] = "db.queryContent: " . $conn->error;
               return $output;
           }
       } else {
           return $output;
       }
   }

   // Fetch each row in associative form and pass it to output.
   while($row = $results->fetch_assoc()) {
       $output["data"][] = $row;
   }
   $results->free();
   $conn->close();
   return $output;
}

// getSite gets specified top site.
function getItem($config, $element, $lang = "en-US") {
   // I should probably turn this into global class
   $output = [
       "err" => '',
       "data" => '',
   ];

   // Connect
   $conn = connect($config);

   // Check if table exists
   if (!checkTable($conn, $element)) {
       $output["err"] = "db.getElement: Table, $element , not found";
       return $output;
   }

   $sql = "SELECT * FROM " . $element . " WHERE lang='$lang'";

   // Results
   $results = $conn->query($sql);

   // If none found stop here
   if ($results->num_rows < 1) {
       $output["err"] = "db.getElement: Non found";
       return $output;
   }

   // Fetch each row in associative form and pass it to output.
   while($row = $results->fetch_assoc()) {
       $output["data"] = $row;
       break;
   }
   $results->free();
   $conn->close();
   return $output;
}
/** upload.php information:
 * This file is called when information is stored in the database.
 * These should be under db
 */
 // Load database functions
 include_once "php/db/db.php";

// insert inserts data into table
function setItem($conn, $table, $items) {
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
function upload($conn, $table, $items) {
    $err = [];

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
	}
	return $err;
}
?>
