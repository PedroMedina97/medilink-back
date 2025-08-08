<?php
include_once './includes/headers.php';

use Classes\Controller;
use Classes\Auth;
use Classes\HTTPStatus;
use Classes\Catalog;
/* $controller = new Controller(); */
$instance = new Catalog();
$auth = new Auth();
$response = null;

// Obtener el token del encabezado Authorization
$token = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : null;

// Decodificar el token si existe
$decoded = !is_null($token) ? $auth->verifyToken($token) : null;

$permissionsArray = [];
if (!is_null($decoded) && isset($decoded->permissions->permissions)) {
    $permissions = $decoded->permissions->permissions;
    $permissionsArray = explode(",", $permissions);
    $permissionsArray = array_map('trim', $permissionsArray);
}

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($router) ? $router->getMethod() : null;

// Verificar si la ruta es diferente de "login"
if ($path !== 'login') {
    // Si el token no existe o no es válido, regresar "401 No autorizado"
    if (is_null($token) || is_null($decoded)) {
        HTTPStatus::setStatus(401);
        $response = [
            "status" => false,
            "msg" => "No autorizado"
        ];
        echo json_encode($response);
        exit();
    }
}

switch ($method) {
    case 'GET':
        switch ($path) {
            case 'getall':
                $table = $router->getParam();
                    $data = $instance->getCatalog($table);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
             
                echo json_encode($response);
            break;
            case 'getdoctors':
                $data = $instance->getDoctors();
                HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
             
                echo json_encode($response);
            break;
            case 'getclients':
                $data = $instance->getClients();
                HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
             
                echo json_encode($response);
            break;
            case 'getclientsbydoctor':
                $id = $router->getParam();
                $data = $instance->getCatalogClientsByIdDoctor($id);
                HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
             
                echo json_encode($response);
            break;
            case 'getsubsidiariesbyuser':
                $userId = $router->getParam();
                
                if (empty($userId)) {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => false,
                        "msg" => "ID de usuario requerido"
                    ];
                } else {
                    $data = $instance->getSubsidiariesByUserId($userId);
                    
                    if ($data && !is_string($data)) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => "Sucursales obtenidas correctamente",
                            "data" => $data
                        ];
                    } else {
                        HTTPStatus::setStatus(404);
                        $response = [
                            "status" => false,
                            "msg" => "No se encontraron sucursales para este usuario"
                        ];
                    }
                }
                
                echo json_encode($response);
            break;
            default:
                HTTPStatus::setStatus(404);
                $response = [
                    "status" => false,
                    "msg" => HTTPStatus::getMessage(404)
                ];
                echo json_encode($response);
            break;     
        }
        break;

   

    default:
        HTTPStatus::setStatus(405);
        $response = [
            "status" => false,
            "msg" => HTTPStatus::getMessage(405)
        ];
        echo json_encode($response);
        break;
}