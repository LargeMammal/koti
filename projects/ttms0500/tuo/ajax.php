<script language ="php">
    $name = "";
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'buy':
                $number = intval($_POST['stuff']);
                //var_dump($number);
                Buy($number);
                break;
            case 'sell':
                $number = intval($_POST['stuff']);
                //var_dump($number);
                Sell($number);
                break;
            case 'money':
                $jsonString = file_get_contents("user/player.json");
                $data = json_decode($jsonString, true);
                echo $data[$data["player"]];
                break;
        }
    }
    
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        
        $jsonString = file_get_contents("user/player.json");
        $data = json_decode($jsonString, true);
        
        $data["player"] = $name;
        
        if (is_null($data[$name])) {
            $data[$name] = 30000;
            echo "Player ", $name, " was created!";
        }
        else {
            $data["Player"] = $name;
            echo "Player ", $name, " was loaded!";
        }
        
        $jsonString = json_encode($data);
        file_put_contents("user/player.json", $jsonString);
    }

    function Sell($number) {
        $jsonString = file_get_contents("user/player.json");
        $data = json_decode($jsonString, true);
        $results = $data["results"];
        
        $thing = $results[$number];
        
        if ($thing["owner"] == $data["player"]) {
            $thing["owner"] = "Computer";
            $data[$data["player"]] += $thing["price"];
            $results[$number] = $thing;

            $data["results"] = $results;
            $jsonString = json_encode($data);
            file_put_contents("user/player.json", $jsonString);

            echo "Item was sold!";
            }
        else {
            echo "You don't own this house!";
        }
    }
    
    function Buy($number) {
        // Opens the player file
        $jsonString = file_get_contents("user/player.json");
        $data = json_decode($jsonString, true);
        // Opens the results array from the data file
        $results = $data["results"];
        
        // Gets certain place from results array
        $thing = $results[$number];
        $raha = $thing["price"];
        
        if ($thing["owner"] == $data["player"]) {
            echo "You already own this place!";
        }
        else {
            //This checks if the current player has enough money.
            if ($raha > $data[$data["player"]]) {
                echo "No can do. Maksaa liikaa.";
            }
            else {
                // Change the owner to current player
                $thing["owner"] = $data["player"];
                $data[$data["player"]] -= $thing["price"];
                $results[$number] = $thing;
                echo "Item was bought!";
            }

            $data["results"] = $results;
            $jsonString = json_encode($data);
            file_put_contents("user/player.json", $jsonString);
        }
        
    }
</script>