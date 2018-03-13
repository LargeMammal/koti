<?php
// TODO: Break the conditioning!!!

// getRootDir gets the root directory of the app.
// This is useful when you need to access files in highly refactored environment. 
function getRootDir() {
    // getcwd gives you the working directory
    $path = getcwd();
    // split the path into an array
    $arr = explode("/", $path);
    // initialize output and stop
    $output = array();
    $stop = false;
    // check each value for 
    foreach ($arr as $value) {
        switch($value) {
            case "php":
            case "projects":
                $stop = true;
            default:
                array_push($output, $value);
        }
        if ($stop == true) { // stop when root is found
            break;
        }
    }
    // implode the array back into string
    return implode("/", $output);
}

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

// This shouldn't be needed anymore
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
