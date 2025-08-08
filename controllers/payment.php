<?php 

use Classes\Controller;
use Classes\Auth;
use Classes\HTTPStatus;
use Classes\Payment;
use Utils\Key;

$controller = new Controller();
$instance = new Payment();
$auth = new Auth();
$name_table = "payments";
$key = new Key();

$token = getallheaders()['Authorization'] ?? null;
$decoded = $token ? $auth->verifyToken($token) : null;

if (!$decoded) {
    HTTPStatus::setStatus(401);
    echo json_encode(["status" => false, "msg" => "No autorizado"]);
    exit();
}

$permissionsArray = [];
if (isset($decoded->permissions->permissions)) {
    $permissionsArray = array_map('trim', explode(',', $decoded->permissions->permissions));
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $router->getMethod();

switch ($method) {
    case 'POST':
        switch ($path) {
            case 'create':
                if(in_array('create_cashcut', $permissionsArray)){
                    HTTPStatus::setStatus(201);
                    $data = $controller->post($instance, $name_table, $body);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(201),
                        "data" => $data
                    ];
                }
                else{
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
            break;

            default:
                echo json_encode(["status" => false, "msg" => "Ruta no válida"]);
            break;
        }
        break;
}
