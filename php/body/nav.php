<?php
// LoadNav loads nav bar. I should use nav as settings bar like in google apps.
function loadNav($config, $nav) {
	$output = "";
	$sql = "SHOW TABLES";

	getItem();

	// If none found stop here
	if ($results->num_rows < 1) {
		$output["err"] = "db.getElement: Non found";
		return $output;
	}

	// Fetch each row in associative form and pass it to output.
	while($row = $results->fetch_assoc()) {
        $str = $row["Tables_in_site"];
        if (!($str == "footer" || $str == "nav" || $str == "head" || $str == "upload" || $str == "not_index")) {
            $output .= '<a href="' . $str . '">'.$str.'</a>';
        }
    }

	$results->free();
    return "<nav>" . $output . $nav["nav"] . "</nav>";
}
?>
