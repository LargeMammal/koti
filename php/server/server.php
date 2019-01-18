<?php
/** server.php holds Server class
* Server object will handle http methods
*/
// Depending on database call specific library
include_once "php/db/db.php";
include_once "php/loadSite.php";

class Server {
    public function serve($config, $langs, $method, $items) {
        $str = "";
        if (count($items) > 1) {
            $str = $this->handleItem($config, $langs, $method, $items);
        } else {
            $str = $this->handleItems($config, $langs, $method, $items);
        }
        return $str;
    }

    private function handleItems($config, $langs, $method, $items) {
        switch($method) {
        case 'GET':
            return loadSite($config, $langs, $items[0], $items[1]);
        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET');
            return "";
        }
    }

    private function handleItem($config, $langs, $method, $items) {
        switch($method) {
        case 'PUT':
            $this->createItem($config, $langs, $items[0], $items[1]);
            return "";

        case 'DELETE':
            $this->deleteItem($config, $langs, $items[0], $items[1]);
            return "";

        case 'GET':
            return $this->displayItem($config, $langs, $items[0], $items[1]);

        default:
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET, PUT, DELETE');
        }
        return "";
    }

    private function createItem($config, $langs, $items, $item){
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

    private function deleteItem($config, $langs, $items, $item) {
        if (isset($this->contacts[$items])) {
            unset($this->contacts[$items]);
            $this->result();
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

    private function displayItem($config, $langs, $items, $item) {
        return loadSite($config, $langs, $items, $item);
    }

    private function paths($url) {
        $uri = parse_url($url); // http://php.net/manual/en/function.parse-url.php
        return $uri['path'];
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
