<?php
/**
 * DBItem
 */
class DBItem {
	public $hash;
	public $title;
	public $date;
	public $item;
	public $tags;
	public $user;
	public $auth;
    
    public $error;

	/**
	 * DBItem
     * 'title' title url endcoded
     * 'user' user id
     * 'item' item to be saved
     * 'auth' authorization level required to view
     * 'tags' associated tags
	 */
	function __construct($array) {
		$this->hash = hash("sha512", $array["item"]);
		$this->title = urldecode($array["title"]);
		$this->date = time();
		$this->item = $array["item"];
		$this->tags = $array["tags"];
		$this->user = $array["user"];
        if (!is_int($array["auth"]) && !ctype_digit($array["auth"])) {
            $this->error = "Malformed request: auth must be int";
        }
        else 
            $this->auth = intval($array["auth"]);
	}

	function __destruct() {
		$this->hash = NULL;
		$this->title = NULL;
		$this->date = NULL;
		$this->item = NULL;
		$this->tags = NULL;
		$this->user = NULL;
		$this->auth = NULL;
	}
}

/**
 * DB class
 * 
 */
class DB {
	private $conn;
	private $database;
	private $output;
	private $pass;
	private $site;
	private $user;
    
    public $error;

	function __construct() {
		$this->user = getenv("USER");
		$this->site = getenv("SITE");
		$this->pass = getenv("PASS");
		$this->database = getenv("DB");
		if (!$this->connect()) 
			trigger_error("Connection failed");
		$this->output = [];
		$this->error = NULL;
		
		// Check items table
		$val = $this->conn->query("select 1 from `items` LIMIT 1");
		if ($val === FALSE) {
			// Create the table
			$sql = "CREATE TABLE items (hash VARCHAR(255) PRIMARY KEY,".
				" user INT UNSIGNED NOT NULL, date BIGINT NOT NULL,".
				" title TEXT NOT NULL, item BLOB NOT NULL,".
				" auth INT UNSIGNED NOT NULL)";
			if ($this->conn->query($sql) !== TRUE) {
				$this->error = "failed to create items table: ".$this->conn->error;
			}
		}
		// Check tags table 
		$val = $this->conn->query("select 1 from `tags` LIMIT 1");
		if ($val === FALSE) {
			// Create the table
			$sql = "CREATE TABLE tags (hash VARCHAR(255) PRIMARY KEY,".
				" tag TEXT NOT NULL)";
			if ($this->conn->query($sql) !== TRUE) {
				$this->error = "db.__construct: failed to create tags table".$this->conn->error;
			}
		}
		// Check tokens table
		$val = $this->conn->query("select 1 from `tokens` LIMIT 1");
		if ($val === FALSE) {
			// Create the table
			$sql = "CREATE TABLE tokens (token VARCHAR(255) PRIMARY KEY,".
				" user INT UNSIGNED NOT NULL";
			if ($this->conn->query($sql) !== TRUE) {
				$this->error = "db.__construct: failed to create tokens table".$this->conn->error;
			}
		}
		if($val->num_rows < 1) {
			// Generate master token
			$master['user'] = 1;
			$master['token'] = $this->generateJWT(
				$master['user'], strtotime('+1 month')
			);
			$master['exp'] = strtotime('+1 month');
			echo ($master['token']);
			if(!$this->SetItem('tokens', $master)) {
				$this->error = "db.__construct: failed to create master token".$this->conn->error;
			}
		}
		// Check users table
		$val = $this->conn->query("select 1 from `users` LIMIT 1");
		if ($val === FALSE) {
			// Create the table
			$sql = "CREATE TABLE items ".
                "(id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,".
				" uname VARCHAR(255) NOT NULL, auth TINYINT NOT NULL,".
				" email VARCHAR(255) NOT NULL, date BIGINT NOT NULL";
			if ($this->conn->query($sql) !== TRUE) {
				$this->error = "db.__construct: failed to create users table".$this->conn->error;
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
				$this->error = "db.__construct: failed to create master user".$this->conn->error;
			}
		}
	}

	function __destruct() {
		$this->conn->close();
		$this->conn = NULL;
		$this->user = NULL;
		$this->site = NULL;
		$this->pass = NULL;
		$this->database = NULL;
		$this->output = NULL;
        $this->error = NULL;
	}
    
	/** 
	 * SetItem inserts data into a table.
	 * Those using SetItem should have special privileges
	 */
	public function SetItem($table, $inputs, $die = 0) : bool {
		// Sanitize inputs
		$items = [];
		// Create assosiative array 
		foreach ($inputs as $key => $value) {
			$var = $this->conn->escape_string($value);
			$items[$key] = $var;
		}
	
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
		
		// Query
		if ($this->conn->query($sql) !== TRUE) {
			trigger_error("db.SetItem: ".$sql."<br>".$this->conn->error);
			return false;
		}
		return true;
	}
	//*/

	/**
	 * @brief
	 * DBGet is the new get function that checks items and tags 
	 * tables for stuff. At this moment this will work as a wrapper.
	 * Eventually this will replace GetItem
	 * @param array inputs array are the search parameters. 
	 * They are what fill the portion after WHERE=
	 * @return array array of results 
	 */
	public function DBGet($inputs): array {
		$output = [];
		$items = [];
		// Clean inputs
		foreach ($inputs as $key => $value) {
			$var = $this->conn->escape_string($value);
			$inputs[$key] = $var;
		}
	
		// Generate query
		$sql = "SELECT * FROM items JOIN tags ON items.hash=tags.hash";

		
		$str = "";
        if (count($inputs) > 1) {
            foreach ($inputs as $key => $var) {
                if ($key % 2 !== 0) continue;
                $items = ["$var[$key]" => $var[$key+1]];
            }
            
            foreach ($items as $column=>$item) {
                if ($str != "") $str .= " AND";
                else $str .= " WHERE";
                $str .= " ".$column."='".$item."'";
            }
        }
		$sql .= $str."GROUP BY items.hash LIMIT 10"; // Make this so that user decides.
	
		$results = $this->conn->query($sql);
		// If query fails stop here
		if ($results === FALSE) {
			$this->error = "db.DBGet: ".$sql."; ".$this->conn->error;
			return $output;
		}
	
		// Fetch each row in associative form and pass it to output.
		while($row = $results->fetch_assoc()) $output[] = $row;
		$results->free();
		
		return $output;
	}
	
	/**
     * @brief 
     * DBGetUser retrieves user if one exists from database
     * @param hash associated with user
     * @return array returns user array
     */
    public function DBGetUser(int $id):array {
		$this->output = [];
		$var = $this->conn->escape_string($id);
	
		// Generate query
		$sql = "SELECT * FROM users WHERE id='$var'";
	
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

	/**
	 * @brief
	 * DBPost funciton for inserting data into items table.
	 * @param DBItem DBItem wrapped data
	 * @return bool returns boolean value indicating success or failure
	 */
	public function DBPost($dbitem): bool {
		// Insert items 
		$sql = "INSERT INTO items (";
		$sql .= "hash, title, date, item, user, auth) VALUES (";
        $sql .= $dbitem->hash.", ";
        $sql .= $this->conn->escape_string($dbitem->title).", ";
        $sql .= $dbitem->date.", ";
        if (is_string($dbitem->item))
            $sql .= $this->conn->escape_string($dbitem->item).", ";
        else
            $sql .= $dbitem->item.", ";
        $sql .= $dbitem->user.", ";
        $sql .= $dbitem->auth.");";
		echo $sql;
	
		// Query
		if ($this->conn->query($sql) !== TRUE) {
			$this->error = "db.SetItem: ".$sql.
				"<br>".$this->conn->error;
			return false;
		}
	
		// insert tags
		$sql = "INSERT INTO tags (";
		$sql .= "hash, tag) VALUES";
        foreach (explode('+', $dbitem['tags']) as $tag) {
            $sql .= ' ('.$dbitem['hash'].','.$tag.'),';
        }
        trim($sql, ',');
		$sql .= ';';
	
		// Query
		if ($this->conn->query($sql) !== TRUE) {
			$this->error = "db.SetItem: ".$sql.
				"<br>".$this->conn->error;
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
	public function DBGetToken($token) : array {
		$this->output = [];
		$var = $this->conn->escape_string($token);
		// Hash tokens in future.
		//$var = $this->conn->escape_string(crypt($token, getenv("SALT")));
	
		// Generate query
		$sql = "SELECT * FROM tokens"; // WHERE token='$var'";
	
		$results = $this->conn->query($sql);
		if ($results !== TRUE) {
            echo ($this->conn->error);
			$this->error = "db.SetItem: ".$this->conn->error;
			return [];
		}
	
        var_dump($results->fetch_assoc());
		// Fetch each row in associative form and pass it to output.
		while($row = $results->fetch_assoc()) $this->output[] = $row;
		$results->free();
		return $this->output[0];
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
	 * @param string table name
	 * @return array returns assoc array of table colums. 
	 */
	public function GetTableFields($t) {
		$fields = [];
		$table = $this->conn->escape_string($t);
		$sql = "DESCRIBE $table";
		$results = $this->conn->query($sql);
		// If query fails stop here
		if ($results === FALSE) {
			trigger_error("db.GetTableFields: ".$sql."; ".$this->conn->error); 
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
}
?>
