<?php
/**
 * DBItem
 */
class DBItem {
	private $hash;
	private $title;
	private $date;
	private $blob;
	private $tags;
	private $user;
	private $auth;

	function __construct($array) {
		$this->hash = hash("sha512", $array["blob"]);
		$this->title = $array["title"];
		$this->date = time();
		$this->blob = $array["blob"];
		$this->tags = $array["tags"];
		$this->user = $array["user"];
		$this->auth = $array["auth"];
	}

	function __destruct() {
		$this->hash = NULL;
		$this->title = NULL;
		$this->date = NULL;
		$this->blob = NULL;
		$this->tags = NULL;
		$this->user = NULL;
		$this->auth = NULL;
	}
}

/**
 * DB class
 * 
 * TODO: If I simplify DB and this, then I should run all 
 * checks in construct. Now I'm wasting cycles. 
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
	* @param array inputs in assosiative array format. Needs 'Table' which 
	* contains the table which is searched. Further information is used
	* for further refinement. TABLE=TABLE WHERE SOMETHING=SOMETHING
	* @param string lang in a string: xx-XX
	* @return array returns results in an array
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
			echo "db.GetItem: Table, " . $items["Table"] . ", not found";
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
		//var_dump($this->output);
		return $this->output;
	}
    
	/** 
	 * SetItem inserts data into a table.
	 * Those using SetItem should have special privileges
	 */
	//*
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
		echo $sql;
	
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
			echo "</br>".$this->conn->error."</br>";
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
		//var_dump($this->conn);

		return true;
	}
	//*/

	/**
	 * DBGet is the new get function that checks items and tags 
	 * tables for stuff. At this moment this will work as a wrapper.
	 * Eventually this will replace GetItem
	 * @param array $search array are the search parameters. 
	 * They are what fill the portion after WHERE=
	 * @return array array of results 
	 */
	public function DBGet($search): array {
		// Check table
		$val = $this->conn->query("select 1 from `items` LIMIT 1");
		// If table doesn't exist
		if ($val === FALSE) {
			// Create the table
			$sql = "CREATE TABLE items (hash VARCHAR(255) PRIMARY KEY,".
				" user INT UNSIGNED NOT NULL, date BIGINT NOT NULL,".
				" title TEXT NOT NULL, blob BLOB NOT NULL,".
				" auth INT UNSIGNED NOT NULL)";
			if ($this->conn->query($sql) !== TRUE) {
				//echo "</br>".$this->conn->error."</br>";
				trigger_error("db.DBGet: ".$this->conn->error);
				return [];
			}
		}
		// Do the search
		$inputs = $search;
		$inputs['Table'] = "items";
		return $this->GetItem($inputs);
	}

	/**
	 * DBPost
	 * @param DBItem insert post in items table. 
	 * @return bool returns boolean value indicating success or failure
	 */
	public function DBPost($dbitem): bool {
		// If table does exist
		if (!$this->checkTable('items')) {
			//trigger_error("db.SetItem: Table, items , not found");
			// Create the table
			$sql = "CREATE TABLE items (hash VARCHAR(255) PRIMARY KEY,".
				" title TEXT NOT NULL, date BIGINT NOT NULL,".
				" blob BLOB NOT NULL, user INT UNSIGNED NOT NULL,".
				" auth INT UNSIGNED NOT NULL)";
			if ($this->conn->query($sql) !== TRUE) {
				trigger_error("db.createTable: ".$this->conn->error);
				return false;
			}
		}

		//* Sanitize inputs
		$items = [];
		// Clean inputs
		foreach ($dbitem as $key => $value) {
			$var = $this->conn->escape_string($value);
			$items[$this->conn->escape_string($key)] = $var;
		}
	
		// Generate query
		$sql = "INSERT INTO items (";
		$sql .= "hash, title, date, blob, user, auth) VALUES (".
			$items['hash'].", ".
			$items['title'].", ".
			$items['date'].", ".
			$items['blob'].", ".
			$items['user'].", ".
			$items['auth'].", ".");";
	
		// Query
		if ($this->conn->query($sql) !== TRUE) {
			trigger_error("db.SetItem: ".$sql.
				"<br>".$this->conn->error);
			return false;
		}
		return true;
	}

	/**
	 * DPGetToken
	 * Get tokens of specific user
	 * @param string token
	 * @return array returns token id pair.
	 */
	//*
	public function DBGetToken($token) : array {
		$this->output = [];
		// If table does exist
		if (!$this->checkTable('tokens')) {
			// Create the table
			$sql = "CREATE TABLE tokens (token VARCHAR(255) PRIMARY KEY,".
				" user INT UNSIGNED NOT NULL, exp BIGINT NOT NULL)";
			if ($this->conn->query($sql) !== TRUE) {
				//echo "</br>".$this->conn->error."</br>";
				trigger_error("db.createTable: ".$this->conn->error);
				return [];
			}
		}
		$check=$this->conn->query("SELECT * FROM tokens");
		var_dump($check);
		if($check->num_rows < 1) {
			// Generate master token
			$master['user'] = 1;
			$master['token'] = $this->generateJWT(
				$master['user'], strtotime('+1 month')
			);
			$master['exp'] = strtotime('+1 month');
			echo ($master['token']);
			if(!$this->SetItem('tokens', $master)) {
				//echo "</br>".$this->conn->error."</br>";
				trigger_error("db.SetItem: ".$this->conn->error);
				return [];
			}
		}
		$var = $this->conn->escape_string($token);
		// Hash tokens in future.
		//$var = $this->conn->escape_string(crypt($token, getenv("SALT")));
	
		// Generate query
		$sql = "SELECT * FROM tokens WHERE token='$var'";
	
		$results = $this->conn->query($sql); 
		if ($results !== TRUE) {
			trigger_error("db.SetItem: ".$this->conn->error);
			return [];
		}
	
		// Fetch each row in associative form and pass it to output.
		while($row = $results->fetch_assoc()) $this->output[] = $row;
		$results->free();
		return $this->output[0];
	}

	//*/
	/**
	 * CheckUserTable
	 * Check/Create user/password table. Necessary for when software is loaded
	 * for the first time. 
	 */
	public function CheckUserTable() {
		$val = $this->conn->query("select 1 from `users` LIMIT 1");
		// If table doesn't exist
		if ($val === FALSE) {
			// Create the table
			$error = $this->createTable('users', ['uname','auth', 'date', 'email']);
			// If creation failed table stop here
			if ($error != "") {
				trigger_error("db.SetItem: " . $error);
				return false;
			}
		}
		if ($val->num_rows < 1) {
			// Generate master user
			$master = array(
				'uname'=>crypt(getenv("MASTER_UNAME"), getenv("SALT")),
				'auth'=>3,
				'date'=>time(),
				'email'=>crypt(getenv("MASTER_EMAIL"), getenv("SALT"))
			);
			if(!$this->SetItem('users', $master)) {
				//echo "</br>".$this->conn->error."</br>";
				trigger_error("db.SetItem: ".$this->conn->error);
				return [];
			}
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
	 * update selected item
	 */
	public function UpdateItem($table, $item) {
		$this->RemoveItem($table, $item);
		$this->SetItem($table, $item);
	}

	/**
	 * GetTableFields gets table fields of given table
	 * 
	 * @return array returns assoc array of table colums. 
	 */
	public function GetTableFields($t) {
		$fields = [];
		$table = $this->conn->escape_string($t);
		// If table doesn't exist stop here
		if (!$this->checkTable($table)) {
			trigger_error("db.GetItem: Table, $table, not found"); 
			return $fields;
		}
		$sql = "DESCRIBE $table";
		$results = $this->conn->query($sql);
		// If query fails stop here
		if ($results === FALSE) {
			trigger_error("db.GetItem: ".$sql."; ".$this->conn->error); 
			return $fields;
		}
		// Fetch each row in associative form and pass it to output.
		while($row = $results->fetch_assoc()) $fields[] = $row;
		$results->free();
		return $fields;
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
	 * base64url_encode
	 */
	private function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 * generateJWT generates JWT from secret 
	 * @param string subject to whom token is assigned to 
	 * @param int unix time when token expires
	 * @return string JWT token in string format
	 */
	//*
	private function generateJWT($subject, $expiration): string {
		// The header
		$header = json_encode([
			'typ' => 'JWT',
			'alg' => 'HS256'
		]);
		// The payload
		$time = time();
		$payload = json_encode([
			'sub' => $subject,
			'iat' => time(),
			'exp' => $expiration
		]);
		// Generate the token
		$data = $this->base64url_encode($header).'.'.$this->base64url_encode($payload);
		$hashedData = hash_hmac('sha256', $data, getenv("HASH_SECRET"), true);
		$signature = $this->base64url_encode($hashedData);
		return $data.'.'.$signature;
	}
	//*/

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
	private function checkTable($table = "") : bool {
		$result = $this->conn->query("select 1 from `$table` LIMIT 1");
		if ($result === FALSE) return false;
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
			case 'title':
			case 'language':
			case 'pw':
			case 'users':
				$items[] = "$column VARCHAR(255) NOT NULL";
				break;
			case 'auth':
			case 'verified':
				$items[] = "$column TINYINT NOT NULL";
				break;
			case 'date':
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
	/// TODO: Remove all hardcoding. 
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