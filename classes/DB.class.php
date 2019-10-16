<?php
/**
 * DB class
 */
class DB {
	private $conn;
	private $database;
	private $output;
	private $pass;
	private $site;
	private $user;

	function __construct($config) {
		$this->user = $config["User"];
		$this->site = $config["Site"];
		$this->pass = $config["Pass"];
		$this->database = $config["Database"];
		if (!$this->connect()) 
			trigger_error("Connection failed");
		$this->output = [];
	}

	function __destruct() {
		$this->conn->close();
		$this->conn = NULL;
		$this->user = NULL;
		$this->site = NULL;
		$this->pass = NULL;
		$this->database = NULL;
		$this->output = NULL;
	}
	
	/** 
	* GetItem gets an item from database
	* Generate the code here and later turn it into a exterrior script
	*/
	public function GetItem($inputs, $lang = NULL): array {
		$this->output = [];
	
		$items = [];
		// Clean inputs
		foreach ($inputs as $key => $value) {
			$var = $this->conn->escape_string($value);
			$items[$this->conn->escape_string($key)] = $var;
		}
	
		// If table doesn't exist stop here
		if (!$this->checkTable($items["Table"])) {
			trigger_error("db.GetItem: Table, ".$items["Table"].
				", not found"); 
			return $this->output;
		}
	
		// Generate query
		$sql = "SELECT * FROM ".$items["Table"];
		$str = "";
		if (isset($lang) && $items["Table"] != "errors") 
			$str = " WHERE Language='" . $lang . "'";
		foreach ($items as $column=>$item) {
			if ($column != "Table") {
				if ($str != "") $str .= " AND";
				else $str .= " WHERE";
				$str .= " ".$column."='".$item."'";
			}
		}
		if ($items["Table"] == "errors") $str .= $str." ORDER BY id DESC";
		$sql .= $str." LIMIT 10"; // Make this so that user decides.
	
		$results = $this->conn->query($sql);
		// If query fails stop here
		if ($results === FALSE) {
			trigger_error("db.GetItem: ".$sql."; ".$this->conn->error); 
			return $this->output;
		}
	
		// Fetch each row in associative form and pass it to output.
		while($row = $results->fetch_assoc()) $this->output[] = $row;
		$results->free();

		return $this->output;
	}
    
	/** 
	 * SetItem inserts data into a table.
	 * Those using SetItem should have special privileges
	 */
	public function SetItem($table, $inputs, $die = 0) : bool {
		//* Sanitize inputs
		$items = [];
		// Create assosiative array 
		foreach ($inputs as $key => $value) {
			$var = $this->conn->escape_string($value);
			$items[$key] = $var;
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
		$sql .= implode(", ", $columns) . ") 
			VALUES (" . implode(", ", $values) . ");";
	
		// If table does exist
		if (!$this->checkTable($table)) {
			trigger_error("db.SetItem: Table, " .
				$table . " , not found");
			// Create the table
			$error = $this->createTable($table, $columns);
			// If creation failed table stop here
			if ($error != "") {
				if ($die != 0) {
					ob_start();
					debug_print_backtrace();
					$dump = ob_get_clean();
					die("db.SetItem: $error.<br>
						<pre>$dump</pre>");
				} else trigger_error("db.SetItem: " . $error);
				return false;
			}
		}
	
		// Query
		if ($this->conn->query($sql) !== TRUE) {
			if ($die != 0) {
				ob_start();
				debug_print_backtrace();
				$dump = ob_get_clean();
				die("db.SetItem: $sql<br>".$this->conn->error.
					"<br><pre>$dump</pre>");
			} 
			else trigger_error("db.SetItem: ".$sql.
				"<br>".$this->conn->error);
			return false;
		}

		return true;
	}
	
	/** 
	 * RemoveItem
	 * remove selected item
	 */
	public function RemoveItem($table, $items) {
		
	}
	
	/** 
	 * UpdateItem
	 * update selected item
	 */
	public function UpdateItem($table, $item) {
		$this->RemoveItem($table, $item);
		$this->SetItem($table, $item);
	}

	/**
	 * LogEvent saves the event to database
	 * This is supposed to handle exceptions, errors and benchmarking.
	 */
	public function LogEvent(
			$errno, 
			$errstr, 
			$errfile = "empty", 
			$errline = 0, 
			$errcontext = NULL
		) {
		$table = "errors";
		ob_start();
		debug_print_backtrace();
		$dump = ob_get_clean();
		$items = [
			"Level" => $errno,
			"Message" => $errstr,
			"File" => $errfile,
			"Line" => $errline,
			"Context" => $dump,
			"Time" => time(),
		];

		if ($errno == E_ERROR || $errno == E_USER_ERROR) {
			echo "<b>Fatal Error: </b> [$errno] '$errstr' 
				in $errfile line $errline with values: 
				<pre>".$dump."</pre><br>";
			die();
		}
		return $this->SetItem($table, $items, 1); 
	}

	/** 
	 * connects to database
	 * returns true if successful
	 */
	private function connect(): bool {
		$this->conn = new \mysqli(
			$this->site, 
			$this->user, 
			$this->pass, 
			$this->database
		);
		if (!$this->conn) {
			$this->output["err"][] = mysqli_connect_error();
			return false;
		}
		return true;
	}

	/** 
	 * checkTable returns true if table exists
	 */
	private function checkTable($items = "") : bool {
		$result = $this->conn->query("SHOW TABLES LIKE '$items'");
		if ($result->num_rows < 1) return false;
		$result->free();
		return true;
	}
    
	/** 
	 * createTitle creates table with given name and data.
	 * First item in array will become primary key
	 */
	private function createTable($table, $columns) {
		$sql = "CREATE TABLE ".$table." (";
		$items = [];
		$items[] = "id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY";
		foreach($columns as $column) {
			switch ($column) {
			case 'Title':
			case 'Language':
			case 'PW':
			case 'UID':
				$items[] = "$column VARCHAR(255) NOT NULL";
				break;
			case 'Auth':
			case 'Verified':
				$items[] = "$column TINYINT NOT NULL";
				break;
			case 'Date':
				$items[] = "$column BIGINT NOT NULL";
				break;
			default:
				$items[] = "$column LONGTEXT NOT NULL";
				break;
			}
		}
		$sql .= implode(", ", $items);
		$sql .= ")";
		if ($this->conn->query($sql) !== TRUE) 
			return "db.createTable: ".$sql.": ".$this->conn->error;
		return "";
	}
	
	/**
	 * InitEditor generates the initial editor used to generate further 
	 * documents.
	 */
	public function InitEditor() {
	   // A quick editor
	   $editor = [
			'Title' => 'Editori',
			'Content' => "<h1>Luo uusi</h1>
				<form action='/content' method='POST'>
				<p><input type='text' 
					name='Title' 
					placeholder='Otsikko' 
					required></p>
				<p><textarea name='Content' 
					placeholder='Sisältö HTML muodossa' 
					required></textarea></p>
				<p><input type='text' 
					name='Category' 
					placeholder='Kategoria englanniksi' 
					required></p>
				<p><input type='text' 
					name='Translation' 
					placeholder='Käännetty kategoria' 
					required></p>
				<p><input type='text' 
					name='Language' 
					placeholder='Kieli xx-XX muodossa' 
					required></p>
				<p>Required authorization(0min and 3max): 
					<input type='number' 
					name='Auth' 
					min='0' max='3' required></p><br>
				<input type='submit'>
				</form>",
			'Category' => 'content',
			'Language' => 'fi-FI',
			'Auth' => 2,
			'Date' => time(),
		];
	
		// Upload editor UI
		$test = $this->GetItem(
			[
				"Table"=>"content",
				"Title"=>"Editori"
			], "fi-FI"
		);
		if (count($test) > 0) return true;
		if ($this->SetItem("content", $editor)) return false;
		return true;
	}
	
	/**
	 * InitFooter initializes footer table
	 */
	public function InitFooter() {
		$lang = "fi-FI";
		$footer_text = "<p>Tein nämä sivut PHP:llä, 
				yrittäen noudattaa REST mallia. 
				Nämä sivut ovat minun testi sivut. 
				https://student.labranet.jamk.fi/~K1729 
				toimii minun CV:nä.</p>";
		$footer = [
			'Language' => $lang,
			'Content' => $footer_text,
		];
	
		if ($this->SetItem("footer", $footer)) return false;
		return true;
	}
}
?>