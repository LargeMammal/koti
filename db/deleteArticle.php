<?php
include 'saveArticle.php';
function deleteArticle($username, $language, $key, $index) {
	chdir('..');
	$pwd = getcwd();
	checkUser($username);
	if (file_exists($pwd."/data/data-". $language .".json")) {
		$json = file_get_contents($pwd."/data/data-". $language .".json");
		$data = json_decode($json);
	}
	else {
		$data = array();
	}
	$array = $data->$key;
	//$array = array_splice($array, $index);
	unset($array[$index]);
	$array = array_values($array);
	$data->$key = $array;
	$json = json_encode($data);
	file_put_contents($pwd."/data/data-". $language .".json", $json);
	echo "success";
}

if(isset($_POST['username']) &&
isset($_POST['language']) &&
isset($_POST['key']) &&
isset($_POST['index'])){
	$username = $_POST['username'];
	$language = $_POST['language'];
	$key = $_POST['key'];
	$index = $_POST['index'];
	deleteArticle($username, $language, $key, $index);
}
?>
