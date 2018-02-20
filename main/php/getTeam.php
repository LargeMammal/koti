<?php
	function getTeam() {
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

		$sql = "SELECT * FROM users";
		$result = $conn->query($sql);
		$json = [];
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				if ($row['admin'] == 1 || $row['editor'] == 1 || $row['blogger'] == 1) {
					$row['password'] = "";
					array_push($json, $row);
				}
			}
		}
		header('Content-Type: application/json');
		$conn->close();
		echo json_encode($json);
	}

	getTeam();
?>
