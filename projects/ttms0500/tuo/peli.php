<!DOCTYPE html>
<html>
    <head>
      <meta name="description" content="I see you" />
      <title>TUO git</title>
      <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
      <meta charset="utf-8">
      <link rel="stylesheet" type="text/css" href="../../../main/css/common.css">
    </head>
    <body>
        <header>
            <h1>Hello!</h1>
            <nav>
                <a href="..">Projektit</a>
            </nav>
        </header>
        <section>
            <section style="padding:10px 40px;"><div id="map"></div></section>
            
            <section style="padding:0px 40px 10px;">
                <div style="text-align:center;">
                    <p id="money"></p>
                    <input type="text" id="name" value="<?php 
                                                        $x = json_decode(file_get_contents('user/player.json'), true); 
                                                        echo $x['player'];?>">
                    <button type="button" onclick="selectPlayer()">Choose player</button>
                </div>
            </section>
        </section>
        
        <script src="js/jquery-3.1.1.min.js"></script>
        <script src="js/siwu.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCJCbgw8Q_LEn-YVFwFTQ3GQZPGfBKtgcc&callback=initMap" async defer></script>
        <footer><p>Mandatory footer.</p></footer>
    </body>
</html>