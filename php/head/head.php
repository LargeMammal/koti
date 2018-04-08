<?php 
// LoadHead returns meta-data stuff. 
function loadHead($content) {
    $str = '';
    $str .= '<meta charset="UTF-8">';
    $str .= '<title>Pääsivu</title>';
    $str .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $str .= '<meta name="description" content="My home page">';
    $str .= '<link rel="stylesheet" type="text/css" href="https://tardigrade.ddns.net/~mammal/css/common.css" />';
    return $str;
}
?>