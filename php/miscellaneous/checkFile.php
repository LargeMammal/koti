<?php
	function checkFile($file) {
		$pwd = "/home/mammal/public_html/";
		if (!file_exists($pwd."main/data/". $file .".json")) {
			$data = array();
			$json = json_encode($data);
			file_put_contents($pwd."main/data/localization-". $language .".json", $json);
			return false;
		} else {
			return true;
		}
	}

	if(isset($_POST['file']) &&
	!empty($_POST['file'])) {
		$file = $_POST['file'];
		if (checkFile($file)) {
			echo "File already exists";
		} else {
			echo "File created";
		}
	}
?>
