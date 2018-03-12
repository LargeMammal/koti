<?php
	function saveToDB($newUser, $newPass, $email) {
		$username = "nm24";
		$password = "298c8e5d4ab9d6b7e84bce83836e3e33";
		$servername = "localhost";
		$dbname = "nordicmedia24";

		$hashedPass = password_hash( $newPass, PASSWORD_DEFAULT);

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
		$username = "nm24";
		$password = "298c8e5d4ab9d6b7e84bce83836e3e33";
		$servername = "localhost";
		$dbname = "nordicmedia24";
		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
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

	if(isset($_POST['username']) &&
	!empty($_POST['username']) &&
	isset($_POST['password']) &&
	!empty($_POST['password']) &&
	isset($_POST['email']) &&
	!empty($_POST['email'])) {
		$username = $_POST['username'];
		$password = $_POST['password'];
		$email = $_POST['email'];
		checkTables();
		saveToDB($username, $password, $email);
	} else {
		echo "Message was not complete";
	}
?>
