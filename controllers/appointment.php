<?php

use Classes\Controller;
use Classes\Auth;
use Classes\HTTPStatus;
use Classes\Appointment;

$controller = new Controller();
$instance = new Appointment();
$auth = new Auth();
$name_table = "appointments";
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
                if (in_array('getall_appointment', $permissionsArray)) {
                    $data = $instance->getAll($name_table);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => "No autorizado"
                    ];
                }
                echo json_encode($response);
                break;

            case 'getbybarcode':
                if (in_array('get_appointment', $permissionsArray)) {
                    $id = $router->getParam();
                    $data = $instance->getByBarcode($id);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
                break;

            case 'getbyid':
                if (in_array('get_appointment', $permissionsArray)) {
                    $id = $router->getParam();
                    $data = $instance->getById($name_table, $id);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
                break;

            case 'getbysubsidiary':
                /* if (in_array('get_appointment', $permissionsArray)) { */
                    $id = $router->getParam();
                    $data = $instance->getBySubsidiary($id);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                /* } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
                } */
                echo json_encode($response);
            break;

            case 'getbypatient':
                if (in_array('get_appointment', $permissionsArray)) {
                    $id = $router->getParam();
                    $data = $instance->getByPatient($id);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
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

    case 'POST':
        switch ($path) {
            case 'setappointment':
                if (in_array('create_appointment', $permissionsArray)) {
                    /* var_dump($body);
                    die(); */
                    $id_order = $body['id_order'];
                    $client = $body['client'];
                    $personal = $body['personal'];
                    $id_subsidiary = $body['id_subsidiary'];
                    $service = $body['service'];
                    $appointment = $body['appointment'];
                    $color = $body['color'];
                    $end_appointment = $body['end_appointment']; // asegúrate de capturarlo también
                    $data = $instance->setAppointment($id_order, $client, $personal, $id_subsidiary, $service, $appointment, $end_appointment, $color);
                    if ($data) {
                        HTTPStatus::setStatus(201);
                        $response = [
                            "status" => "success",
                            "msg" => HTTPStatus::getMessage(201),
                            "data" => $data
                        ];
                    } else {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => "error",
                            "msg" => HTTPStatus::getMessage(400),
                            "data" => $data
                        ];
                    }
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
                break;
            case 'create':
                if (in_array('create_appointment', $permissionsArray)) {
                    HTTPStatus::setStatus(201);
                    $data = $controller->post($instance, $name_table, $body);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(201),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
                break;
            default:
                echo "Método no definido para esta clase";
                break;
        }
        break;

    case 'PUT':
        switch ($path) {
            case 'update':
                if (in_array('update_appointment', $permissionsArray)) {
                    HTTPStatus::setStatus(200);
                    $data = $controller->update($instance, $name_table, $body);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
                break;

            default:
                echo "Método no definido para esta clase";
                break;
        }

        break;

    case 'DELETE':
        switch ($path) {
            case 'delete':
                if (in_array('delete_appointment', $permissionsArray)) {
                    $data = $controller->delete($instance, $name_table);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
                break;

            default:
                echo "Método no definido para esta clase";
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
