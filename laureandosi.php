<?php

/**

* Template Name: laureandosi

*/

use laureandosi\API;

if (empty($_REQUEST["api"])) {
    $html = file_get_contents("src/page/home/index.html", 1);
    $js = file_get_contents("src/page/home/index.js", 1);
    $css = file_get_contents("src/page/home/index.css", 1);

    $html = str_replace('<link rel="stylesheet" href="index.css">', "<style>" . $css . "</style>", $html);
    $html = str_replace('<script src="index.js"></script>', "<script>" . $js . "</script>", $html);

    echo $html;
} else {
    require("src/class/API.php");

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json; charset=utf-8');
    $fun = $_REQUEST["api"];
    if (method_exists(API::class, $fun)) {
        echo (API::getInstance())::$fun(file_get_contents('php://input'));
    } else {
        http_response_code(404);
    }
}
