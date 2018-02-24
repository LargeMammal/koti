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
				if ($row['blogger'] == 0 && $row['editor'] == 0) {
					echo "unauthorized";
					die("unauthorized");
				}
			}
		}
	}

	function saveArticle($type, $language, $article) {
		chdir('..');
		$pwd = getcwd();
		checkUser($article['username']);
		if (file_exists($pwd."/data/data-". $language .".json")) {
			$json = file_get_contents($pwd."/data/data-". $language .".json");
			$data = json_decode($json);
		}
		else {
			$data = array();
		}
		$data->$type[] = $article;
		$json = json_encode($data);
		file_put_contents($pwd."/data/data-". $language .".json", $json);
		echo "success";
	}

	if(isset($_POST['type']) &&
	!empty($_POST['type']) &&
	isset($_POST['article']) &&
	!empty($_POST['article'])){
		$type = $_POST['type'];
		$language = $_POST['language'];
		$article = $_POST['article'];
		//echo dirname('__FILE__', 1);
		saveArticle($type, $language, $article);
	}
?>
