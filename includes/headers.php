<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json'); 
header("Access-Control-Allow-Headers: Authorization, Content-Type, Origin");
header("Access-Control-Allow-Methods: OPTIONS,GET,PUT,POST,DELETE,FILES");

// Si la solicitud es OPTIONS, simplemente responde con los encabezados CORS y un estado 200
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$body = json_decode(file_get_contents("php://input"), true);