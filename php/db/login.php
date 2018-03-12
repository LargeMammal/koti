<?php
	function getHash($user) {
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
			$row = $result->fetch_assoc();
			return $row["password"];
		}
		return "";
	}

	function checkRole($username, $role) {
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
			$row = $result->fetch_assoc();
			if ($row[$role] == 1) {
				return true;
			}
		}
		return false;
	}

	if(isset($_POST['username']) &&
	!empty($_POST['username']) &&
	isset($_POST['password']) &&
	!empty($_POST['password'])){
		$username = $_POST['username'];
		$password = $_POST['password'];
		if(password_verify($password, getHash($username))) {
			$response = array();
			$response["login"] = "success";
			if (checkRole($username, "editor")) {
				$response["editor"] = 1;
			}
			echo json_encode($response);
		}
		else {
			echo "Either user doesn't exist or password was wrong";
		}
	}
?>
