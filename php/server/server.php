<?php
/** server.php holds Server class
* Server object will handle http methods
*/
// Depending on database call specific library
include_once "php/db/db.php";
include_once "php/loadSite.php";

class Server {
    public function serve($config, $method, $items, $langs) {
        if (count($items) > 1) {
            $this->handleItem($config, $method, $items, $langs);
        } else {
            $this->handleItems($config, $method, $items, $langs);
        }
    }

    private function handleItems($config, $method, $items, $langs) {
        $output = loadSite();
        switch($method) {
        case 'GET':
            $this->result();
            break;
        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET');
            break;
        }
    }

    private function handleItem($config, $method, $items, $langs) {
        switch($method) {
        case 'PUT':
            $this->createItem($items);
            break;

        case 'DELETE':
            $this->deleteItem($items);
            break;

        case 'GET':
            $this->displayItem($items);
            break;

        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET, PUT, DELETE');
            break;
        }
    }

    private function createItem($items){
        if (isset($this->contacts[$items])) {
            header('HTTP/1.1 409 Conflict');
            return;
        }
        /* PUT requests need to be handled by reading from standard input.
         * php://input is a read-only stream that allows you to read raw
         * data from the request body.
         */
        $data = json_decode(file_get_contents('php://input'));
        if (is_null($data)) {
            header('HTTP/1.1 400 Bad Request');
            $this->result();
            return;
        }
        $this->contacts[$items] = $data;
        $this->result();
    }

    private function deleteItem($items) {
        if (isset($this->contacts[$items])) {
            unset($this->contacts[$items]);
            $this->result();
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

    private function displayItem($items) {
        if (array_key_exists($items, $this->contacts)) {
            echo json_encode($this->contacts[$items]);
        } else {
		 //           header('HTTP/1.1 404 Not Found');
		header('HTTP/1.1 404 Not Found', true, 404);
		echo "The file you're looking for does not exist.";
        }
    }

    private function paths($url) {
        $uri = parse_url($url); // http://php.net/manual/en/function.parse-url.php
        return $uri['path'];
    }

    /**
     * Displays a list of all contacts.
     */
    private function result($input, $type) {
        header('Content-type: application/json');
        echo json_encode($this->contacts);
    }
}

function parseLang($str) {
    $output = [];
    // Split the string
    $arr = explode(";", $str);
    foreach ($arr as $value) {
        // ignore q thingys
        foreach (explode(",", $value) as $val) {
            if (false === strpos($val, "q=")) {
                $output[] = $val;
                break;
            }
        }
    }
    return $output;
}
?>
