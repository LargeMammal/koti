<?php
	function checkUser($user) {
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

		$sql = "SELECT * FROM users WHERE username = '" . $user . "'"; // Noitten on pakko olla tuossa.
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				if ($row['admin'] != 1) {
					echo "unauthorized";
					die("unauthorized");
				}
			}
		}
		$conn->close();
	}

	function boolToInt($val) {
		if ($val == "true") {
			return 1;
		} else {
			return 0;
		}
	}

	function changePrivileges($user, $roles) {
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

		$sql = "UPDATE users SET admin=".boolToInt($roles[0]).", editor=".boolToInt($roles[1]).", blogger=".boolToInt($roles[2])." WHERE username = '" . $user . "'"; // Noitten on pakko olla tuossa.

		if ($conn->query($sql) === TRUE) {
			echo "success";
		} else {
			echo $conn->error;
		}
		$conn->close();
	}

	if(isset($_POST['action']) &&
	!empty($_POST['action']) &&
	isset($_POST['roles']) &&
	!empty($_POST['roles']) &&
	isset($_POST['username']) &&
	!empty($_POST['username'])) {
		$action = $_POST['action'];
		$roles = $_POST['roles'];
		$account = $_POST['account'];
		$username = $_POST['username'];
		checkUser($username);
		changePrivileges($account, $roles);
	}
?>
