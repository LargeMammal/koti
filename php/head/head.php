<?php
// LoadHead returns meta-data stuff.
function loadHead($content) {
    $description = $title = "";
    if (isset($content)) {
        $title = $content["Title"];
        $description = $content["Description"];
    }
    $str = '';
    $str .= '<meta charset="UTF-8">';
    $str .= '<title>' . $title . '</title>';
    $str .= '<meta name="description" content="' . $description . '">';
    $str .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    //$str .= '<link rel="stylesheet" type="text/css" href="/css/common.css" />';
    $str .= '<link rel="stylesheet" type="text/css" href="https://tardigrade.ddns.net/css/common.css" />';
    return $str;
}
?>
