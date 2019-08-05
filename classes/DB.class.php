<?php
/**
 * DB class
 */
class DB {
    private $conn;
    private $user;
    private $site;
    private $pass;
    private $database;
    private $output;

    function __construct($config) {
        $this->$user = $config["User"];
        $this->$site = $config["Site"];
        $this->$pass = $config["Pass"];
        $this->$database = $config["Database"];
        $this->output = [
			"err" => [],
			"data" => [],
		];
    }

    function __destruct() {
        $this->conn = NULL;
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
	public function GetItem($inputs, $lang = NULL): array{
		if (!connect()) return $this->output;
	
		//* Sanitize input
		$items = [];
		// Create assosiative array 
		foreach ($inputs as $key => $value) $items[$conn->escape_string($key)] = $conn->escape_string($value);
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
	
		$results = $this->conn->query($sql);
		// If query fails stop here
		if ($results === FALSE) {
			$this->output["err"][] = "db.getItem: ".$sql."; ".$conn->error;
			return $this->output;
		}
	
		// Fetch each row in associative form and pass it to output.
		while($row = $results->fetch_assoc()) $output["data"][] = $row;
		$results->free();

		$this->conn->close();
		return $this->output;
	}
    
    /** SetItem
     * SetItem inserts data into a table.
     * Those using setItem should have special privileges
     */
	public function SetItem($table, $inputs) {
        if (!connect()) return $this->output;
	
		//* Sanitize inputs
		$items = [];
		// Create assosiative array 
		foreach ($inputs as $key => $value) $items[$this->conn->escape_string($key)] = $this->conn->escape_string($value);
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
				$this->output["err"][] = "db.setItem: " . $error;
				return $this->output;
			}
		}
	
		// Query
		if ($this->conn->query($sql) !== TRUE) $this->output["err"][] = "db.setItem: ".$sql."<br>".$this->conn->error;

		$this->conn->close();
		return $output;
	}
	
	/** RemoveItem
     * remove selected item
     */
	public function RemoveItem($table, $item) {
		
	}
	
	/** UpdateItem
     * update selected item
     */
	public function UpdateItem($table, $item) {
		$this->RemoveItem($table, $item);
		$this->SetItem($table, $item);
	}

	/** connect
	 * connect creates Connection
     * returns true if successful
	 */
	private function connect(): bool {
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
	private function createTable($table, $columns) {
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
		if ($this->conn->query($sql) !== TRUE) {
			return "db.createTable: ". $sql. ": " . $this->conn->error;
		}
		return "";
	}
}
?>