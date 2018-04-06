<?php
function parseLang($str) {
    $output = [];
    // Split the string ´
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