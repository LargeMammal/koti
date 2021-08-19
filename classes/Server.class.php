<?php
/** 
 * Server.class.php holds Server class
 * Server object will handle http methods
 */

class Server {
        public $error;

        private $method;
        //private $post;
        private $get;
        
        private $items;
        private $db;
        private $type; // html/json/xml
        private $server;

        /**
         * @brief
         * Site object gathers all information needed to handle a request
         * 
         * @param DB my database object that creates 
         * relatively easy database interface
         * @param array assoc array of server parameters
         * @param array assoc array of get variables
         * @param array assoc array of post variables
         */
        function __construct(
                array $server, 
                array $get = NULL, 
                array $post = NULL
        ){
                $this->error = [];

                $this->method = $server['REQUEST_METHOD'];
                //$this->post = $post;
                $this->get = $get;

                $this->items = [];
                $this->db = new DB();
                if (!empty($this->db->error)) {
                    $this->error[] = $this->db->error;
                }
                $this->type = "html";
                $this->server = $this->paths($server['REQUEST_URI']);
                if (empty($this->server)) $this->server = ["title", "index"];
                $n = count($this->server);
                if (($n > 1) && ($n % 2 !== 0))
                        $this->error[] = "Malformed request!";
        }
        
        function __destruct() 
        {
                $this->method = NULL;
                $this->post = NULL;
                $this->get = NULL;

                $this->items = NULL;
                $this->items = NULL;
                $this->db = NULL;
                $this->type = NULL;
                $this->server = NULL;
        }

        public function Serve() {
                $output = "";
                switch($this->method) {
                case 'GET':
                        $output = $this->get();
                        break;
                case 'POST':
                        $this->post();
                        break;
                case 'DELETE':
                        $this->delete();
                        break;
                default:
                        http_response_code(405);
                        header('Allow: GET POST');
                        break;
                }
                return $output;
        }
        
        /**
         * @brief
         * Get function handles get requests. Adds necessary headers.
         * More accurately it formats the request. Request is done in 
         * construction. 
         * @return string Get function returns site in string form
         */
        private function get() : string
        {
		if ($this->server[0] === "GetToken" && isset($_GET['token'])) {
			// Get token id pair that matches token in request
			$token = $this->db->DBGetToken($_GET['token']);
			if (!empty($this->db->error)) {
				$this->error=array_merge($this->error, $this->db->error);
				http_response_code(500);
				return;
			}
			
			if (empty($token)) {
				$this->error[] = 'Wrong token';
				http_response_code(403);
				return;
			}
		}
                $str = '<!DOCTYPE html><head>';
                
                if(isset($this->get['token']))
                        $token = $this->get['token'];
                $this->items = $this->db->DBGet($this->server);
                if (!empty($this->db->error)) {
                        $this->error = array_merge($this->error, $this->db->error);
                        return "";
                }
                if (empty($this->items)) 
                        $this->items[] = ["title", "empty"];

                switch ($this->type) {
                        case 'json':
                                header('Content-Type: application/json');
                                return json_encode($this->items);
                        case 'xml':
                                header('Content-Type: text/xml');
                                $xml = new SimpleXMLElement('<root/>');
                                array_flip($this->items);
                                array_walk_recursive($this->items, array($xml, 'addChild'));
                                return $xml->asXML();
                        default:
                                # code...
                                break;
                }

                // Stuff in head
                $str .= $this->loadHead();
                $str .= '</head>';
		$str .= '<script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>';
		$str .= '<script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>';
                // Stuff in body
                $str .= '<body><div id="root"><div><header>'.$this->loadHeader();
                $nav = $this->db->DBGet(["title", "nav"]);
                array_filter($nav);
                if (!empty($nav))
                        $str .= '<nav>'.$nav[0].'</nav></header>';
                else $str .= '<nav>empty</nav></header>';
                $str .= '<section><noscript>I use JavaScript for extra functionality, '.
			'site should work well enough without it</noscript>'.$this->loadBody();
                $str .= "\n".implode("\n",$this->db->error).'</section>';
                $footer = $this->db->DBGet(["title", "footer"]);
                array_filter($footer);
                if (!empty($footer))
                        $str .= '<footer>'.$footer[0].'</footer>';
                else $str .= '<footer>empty</footer>';
                $str .= '</div></div></body></html>';
                return $str;
        }

        /**
         * @brief
         * Post function handles post requests.
         */
        private function post()
        {
                if (empty($_POST)) {
                        $this->error[] = 'Empty request'; 
                        return;
                }
                
                switch ($this->server[0]) {
			case 'resgister':
				// request registeration
				$query = [];
				$query['user'] = $this->server[1];
				$query['platform'] = $_POST['platform'];
				$query['browser'] = $_POST['browser'];
				$query['version'] = $_POST['version'];
				break;
			default:
				// perus koodi
		}
                
                if ($_POST["token"] === NULL) {
                        $this->error[] = 'Missing token';
                        return;
                }
                if ((count($this->server) < 2) || $this->server[0] !== 'title') {
                        $this->error[] = 'Malformed request';
                        return;
                }

                // Get token id pair that matches token in request
                $token = $this->db->DBGetToken($_POST['token']);
                if (!empty($this->db->error)) {
                        $this->error=array_merge($this->error, $this->db->error);
                        http_response_code(500);
                        return;
                }
                
                if (empty($token)) {
			$this->error[] = 'Wrong token';
			http_response_code(403);
			return;
                }
                
                $query = NULL;
                $query['title'] = $this->server[1];
                $query['user'] = $token[0]['user']; // token 'user' is just user id number
                $query['item'] = $_POST['item'];
                $query['auth'] = $_POST['auth'];
                $query['tags'] = $_POST['tags'];
                //$q = $query;
                //var_dump($q);
                $dbitem = new DBItem($query);
                if (!empty($dbitem->error)) {
                        $this->error=array_merge($this->error, $dbitem->error);
                        http_response_code(500);
                        return;
                }
                //echo "dbitem done\n";
                if (!$this->db->DBPost($dbitem)) {
                        $this->error=array_merge($this->error, $this->db->error);
                        http_response_code(500);
                        return;
                }
        }

        /**
         * Delete function handles delete requests.
         * 
         * @return void Delete only generates response code
         */
        private function delete()
        {
        }

        /** 
         * loadHead function should generate the head elements.
         * Later I should add meta handling
         */
        private function loadHead() 
        {
                $title = "empty";
                if (isset($this->items))
                        if (!empty($this->items))
                                $title = $this->items[0]['title'];
                                
                $str = '<meta charset="UTF-8">';
                $str .= "<title>$title</title>";
                $str .= '<meta name="viewport" 
                        content="width=device-width, initial-scale=1.0">';
                $str .= '<link rel="icon" type="image/png" href="/image.png">';
                $str .= '<link rel="stylesheet" type="text/css" 
                        href="/css/common.css" >';
                return $str;
        }

        /** 
         * loadHeader will in future generate custom header
         */
        private function loadHeader() 
        {
                $banner = "";
                if (is_null($this->items)) 
                        return "<h1>empty</h1>"; 
                if (count($this->items) == 1) 
                        $banner = $this->items[0]['title'];
                $output = "<h1>$banner</h1>";
                return $output;
        }

        /** loadBody
         * loadBody will generate content section of the page
         */
        private function loadBody() 
        {
                $content = "";
                if (empty($this->items)) 
                        return "<h1>Site came up empty!</h1>";
                foreach ($this->items as $items) {
			$content .= "<section>";
			$content .= $items['item'];
			$content .= "</section>";
                }
                return $content;
        }

        /**
         * paths extracts file path and get variables from URI
         * 
         * @param string URI in string form 
         * @return array returns array of URI elements. 
         */
        private function paths(string $url):array
        {
                if ($url === "") return [];
                // Get get variables. 
                $vget = explode('?', trim($url, "/"));
                $type = explode('.', $vget[0]);
                if ($vget[0] == "") return [];
                if (count($type) > 1) {
                        switch ($type[count($type)-1]) {
                        case 'json':
                                $this->type = 'json';
                                unset($type[count($type)-1]);
                                $vget[0] = implode('.', $type);
                                break;
                        case 'xml':
                                $this->type = 'xml';
                                unset($type[count($type)-1]);
                                $vget[0] = implode('.', $type);
                                break;
                        case 'test':
                                $this->type = 'test';
                                unset($type[count($type)-1]);
                                $vget[0] = implode('.', $type);
                                break;
                        default:
                                throw new Exception("file type not recognized");
                                break;
                        }
                }
                $items = explode("/", $vget[0]);
                return $items;
        }
        
        /**
	 * detectDevice gets device info 
	 * and saves it into database
	 */
	private function detectDevice() {
		
	$browser = new Browser();

	if ($browser->getName() === Browser::IE && $browser->getVersion() < 11) {
		echo 'Please upgrade your browser.';
	}

		return;
	}
}
?>
