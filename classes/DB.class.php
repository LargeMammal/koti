<?php
/**
 * DB class
 */
class DB {
    private $conn;
    private $config;
    private $user;
    private $site;
    private $pass;
    private $database;
    private $output;

    function __construct($config) {
        $this->config = $config;
        $this->$user = $this->config["User"];
        $this->$site = $this->config["Site"];
        $this->$pass = $this->config["Pass"];
        $this->$database = $this->config["Database"];
        $this->output = [
			"err" => [],
			"data" => [],
		];
    }

    function __destruct() {
        $this->conn = NULL;
        $this->config = NULL;
        $this->$user = NULL;
        $this->$site = NULL;
        $this->$pass = NULL;
        $this->$database = NULL;
        $this->output = NULL;
    }
	
	/** getItem
	* getItem gets an item from database
	* Generate the code here and later turn it into a exterrior script
	*/
	public function GetItem($database, $inputs, $lang = NULL){
		if (connect()) return $this->output;
	
		//* Sanitize input
		$items = [];
		foreach ($inputs as $key => $value) {
			$items[$conn->escape_string($key)] = $conn->escape_string($value);
		}
		//*/
	
		// If table doesn't exist stop here
		if (!checkTable($items["Table"])) {
			$this->output["err"][] = "db.getItem: Table, ".$items["Table"]." , not found";
			return $this->output;
		}
	
		// Generate query
		$sql = "SELECT * FROM ".$items["Table"];
		$str = "";
		if (isset($lang)) {
			 $str = " WHERE Language='" . $lang . "'";
		}
		foreach ($items as $column=>$item) {
			if ($column != "Table") {
				if ($str != "") $str .= " AND";
				else $str .= " WHERE";
				$str .= " ".$column."='".$item."'";
			}
		}
		$sql .= $str." LIMIT 10";
	
		$results = $conn->query($sql);
		// If query fails stop here
		if ($results === FALSE) {
			$output["err"][] = "db.getItem: ".$sql."; ".$conn->error;
			return $output;
		}
	
		// Fetch each row in associative form and pass it to output.
		while($row = $results->fetch_assoc()) {
			$output["data"][] = $row;
		}
		$results->free();
		return $output;
	}
    
    /** SetItem
     * SetItem inserts data into a table.
     * Those using setItem should have special privileges
     */
	public function SetItem($table, $inputs) {
        if (connect()) return $this->output;
	
		//* Sanitize inputs
		$items = [];
		foreach ($inputs as $key => $value) {
			$items[$conn->escape_string($key)] = $conn->escape_string($value);
		}
		//*/
	
		// Generate query
		$sql = "INSERT INTO ". $table ."(";
		$columns = [];
		$values = [];
		foreach ($items as $column=>$item) {
			$columns[] = $column;
			$values[] = "'" . $item . "'";
		}
		$sql .= implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");";
	
		// If table does exist
		if (!checkTable($table)) {
			$output["err"][] = "db.setItem: Table, " . $table . " , not found";
			// Create the table
			$error = createTable($conn, $table, $columns);
			// If creation failed table stop here
			if ($error != "") {
				$output["err"][] = "db.setItem: " . $error;
				return $output;
			}
		}
	
		// Query
		if ($conn->query($sql) !== TRUE) {
			$output["err"][] = "db.setItem: " . $sql . "<br>" . $conn->error;
		}
		return $output["err"];
	}
	
	/** RemoveItem
     * remove selected item
     */
	public function RemoveItem($items, $item) {
	
	}

	/** connect
	 * connect creates Connection
     * returns true if successful
	 */
	private function connect($database) {
		$this->conn = new \mysqli($this->Site, $this->user, $this->pass, $this->database);
		if (!$this->conn) {
            $this->output["err"][] = mysqli_connect_error();
            return false;
		}
		return true;
	}

	/** checkTable
     * checkTable returns true if table exists
     */
	private function checkTable($items = "") {
		$result = $this->conn->query("SHOW TABLES LIKE '$items'");
		if ($result->num_rows < 1) {
			return false;
		}
		$result->free();
		return true;
	}
    
    /** createTable
     * createTitle creates table with given name and data.
     * First item in array will become primary key
     */
	function createTable($conn, $table, $columns) {
		$sql = "CREATE TABLE ".$table." (";
		$items = [];
		$items[] = "id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY";
		// All this should be given in an array
		foreach($columns as $column) {
			if ($column == "Title" || $column == "Language" || $column == "PW" || $column == "UID") {
				$items[] = "$column VARCHAR(255) NOT NULL";
			} elseif ($column == "Auth" || $column == "Verified") {
				$items[] = "$column TINYINT NOT NULL";
			} elseif ($column == "Date") {
				$items[] = "$column BIGINT NOT NULL";
			} else {
				$items[] = "$column LONGTEXT NOT NULL";
			}
		}
		$sql .= implode(", ", $items);
		$sql .= ")";
		if ($conn->query($sql) !== TRUE) {
			return "db.createTable: ". $sql. ": " . $conn->error;
		}
		return "";
	}
}
?>