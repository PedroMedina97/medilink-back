<?php
include 'includes/headers.php';
require 'vendor/autoload.php';

use Classes\Router;



$router = new Router();

$controlador = $router->getController();
$method = $router->getMethod();
$param = $router->getParam();
$extra = $router->getExtra();

/* var_dump("controller: ".$controlador);
var_dump("method: ".$method);
var_dump("param: ".$param);
var_dump("extra: ".$extra);
die(); */


if (file_exists('controllers/' . $controlador . '.php')) {
    require 'controllers/' . $controlador . '.php';
} else {
    require 'controllers/not_found.php';
}

?>