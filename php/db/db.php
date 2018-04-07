<?php
// createTitle creates title table
// Do I need this?
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
// getSite gets specified top site.
function getSite($db, $top = "", $sub = "") { 
    $output = [
        "err" => "", 
        "result" => [],
    ];
	$conn = new mysqli($db["Site"], $db["User"], "", $db["Database"]);
	if ($conn->connect_error) {
		echo "Failed to connect to MySQL: (" . $conn;
	}

	if ($top == "") {
		$top = "index";
	}

	$sql = "SELECT * FROM information_schema.tables WHERE table_schema = 'site' AND table_name = 'title' LIMIT 1;";

	$result = $conn->query($sql);
	if ($result->num_rows < 1) {
		echo "Table not found. Creating one.<br>";
		$sql = "CREATE TABLE 'site' (".
			"title VARCHAR(64) NOT NULL PRIMARY KEY, ".
			"password VARCHAR(255) NOT NULL, ".
			"email VARCHAR(64) NOT NULL, ".
			"full_name VARCHAR(128), ".
			"image VARCHAR(255), ".
			"admin TINYINT(1) NOT NULL, ".
			"editor TINYINT(1) NOT NULL, ".
			"blogger TINYINT(1) NOT NULL, ".
			"reg_date TIMESTAMP);";
		if ($conn->query($sql) === TRUE) {
			echo "Table users created successfully";
		} else {
			echo "Error creating table ". $conn->error;
		}
	}
	return $output;
}

// InsertDB inserts something into database
function InsertDB($newUser, $newPass, $email) {
	$username = "site";
	$servername = "localhost";
	$dbname = "site";

	//$hashedPass = password_hash( $newPass, PASSWORD_DEFAULT);

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "INSERT INTO users(username, password, email, admin, editor, blogger) VALUES ('" . $newUser . "', '" . $hashedPass . "', '".$email."', 0, 0, 0)";

	if ($conn->query($sql) === TRUE) {
		echo "success";
	} else {
		echo "Error: ". $conn->error;
	}
	$conn->close();
}

function checkTables() {
	$username = "site";
	$servername = "localhost";
	$dbname = "nordicmedia24";
	// Create connection
	$conn = new mysqli($servername, $username, "", $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	$result = $conn->query("SHOW TABLES LIKE 'users'");
	if ($result->num_rows < 1) {
		$sql = "CREATE TABLE 'users' (".
			"username VARCHAR(64) NOT NULL PRIMARY KEY, ".
			"password VARCHAR(255) NOT NULL, ".
			"email VARCHAR(64) NOT NULL, ".
			"full_name VARCHAR(128), ".
			"image VARCHAR(255), ".
			"admin TINYINT(1) NOT NULL, ".
			"editor TINYINT(1) NOT NULL, ".
			"blogger TINYINT(1) NOT NULL, ".
			"reg_date TIMESTAMP)";
		if ($conn->query($sql) === TRUE) {
			echo "Table users created successfully";
		} else {
			echo "Error creating table ". $conn->error;
		}
	}
	$conn->close();
}
?>
