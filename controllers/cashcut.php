<?php

use Classes\Controller;
use Classes\Auth;
use Classes\HTTPStatus;
use Classes\CashCut;
use Utils\Key;

$controller = new Controller();
$instance = new CashCut();
$auth = new Auth();
$name_table = "cash_cuts";
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
    case 'GET':
        switch ($path) {
            case 'getall':
                if (in_array('getall_cashcut', $permissionsArray)) {
                    $data = $instance->getAllCashCuts();
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

            case 'getbyid':
                if (in_array('get_cashcut', $permissionsArray)) {
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
            case 'cashcut-gains':
                $month = $router->getParam();
                /* $month = $_GET['month'] ?? date('Y-m');  */
                $data = $instance->getGains($month);
                if ($data && !isset($data['error'])) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => "error",
                        "msg" => $data['error'] ?? HTTPStatus::getMessage(400),
                        "data" => []
                    ];
                }
                echo json_encode($response);
                break;

            case 'cashcut-payments':
                $id = $router->getParam();
                $data = $instance->getPaymentsByIdCashcut($id);
                if ($data) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(403);
                    $response = [
                        "status" => "error",
                        "msg" => HTTPStatus::getMessage(403),
                        "data" => $data
                    ];
                }
                echo json_encode($response);
                break;
            case 'cashcut-by-doctor':
                if (in_array('get_cashcut', $permissionsArray)) {
                    $doctorId = $router->getParam();
                    $data = $instance->getCashCutsByDoctor($doctorId);
                    if ($data) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => HTTPStatus::getMessage(200),
                            "data" => $data
                        ];
                    } else {
                        HTTPStatus::setStatus(403);
                        $response = [
                            "status" => "error",
                            "msg" => HTTPStatus::getMessage(403),
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
            case 'cashcut-payments-excel':
                $id = $router->getParam();
                $data = $instance->getPaymentsByIdCashcutExcel($id);
                if ($data) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(403);
                    $response = [
                        "status" => "error",
                        "msg" => HTTPStatus::getMessage(403),
                        "data" => $data
                    ];
                }
                echo json_encode($response);
                break;

            case 'cashcut-export-range':
                $dates = $router->getParam();
                $instance->getCashCutsGroupedWithPayments($dates);
                exit;
                break;

            case 'cashcut-percent':
                $data = $instance->getPercentSubsidiary();
                if ($data) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(403);
                    $response = [
                        "status" => "error",
                        "msg" => HTTPStatus::getMessage(403),
                        "data" => $data
                    ];
                }
                echo json_encode($response);
                break;
            case 'cashcut-top-services':
                $data = $instance->getTopServices();
                HTTPStatus::setStatus(200);
                echo json_encode(["status" => "success", "data" => $data]);
                break;

            case 'data-home':
                $data = $instance->getDataHome();
                if ($data) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(403);
                    $response = [
                        "status" => "error",
                        "msg" => HTTPStatus::getMessage(403),
                        "data" => $data
                    ];
                }
                echo json_encode($response);
                break;
            case 'gains-week':
                $data = $instance->getGainsWeek();
                if ($data) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => HTTPStatus::getMessage(200),
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(403);
                    $response = [
                        "status" => "error",
                        "msg" => HTTPStatus::getMessage(403),
                        "data" => $data
                    ];
                }
                echo json_encode($response);
                break;
            default:
                echo json_encode(["status" => false, "msg" => "Ruta no válida"]);
                break;
        }
        break;
    case 'POST':
        switch ($path) {
            case 'create':
                if (in_array('create_cashcut', $permissionsArray)) {
                    HTTPStatus::setStatus(201);
                    $data = $instance->setCashcut($body);
                    /* var_dump($data);
                    die(); */
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

            case 'update-total':
                if (in_array('create_cashcut', $permissionsArray)) {
                    HTTPStatus::setStatus(201);
                    $data = $instance->updateTotal($body);
                    /* var_dump($data);
                    die(); */
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
                echo json_encode(["status" => false, "msg" => "Ruta no válida"]);
                break;
        }
        break;
}
