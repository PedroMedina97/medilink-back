<?php
include_once './includes/headers.php';

use Classes\Auth;
use Classes\MedicalHistory;
use Classes\HTTPStatus;

$instance = new MedicalHistory();
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

// Verificar autenticación para todas las rutas
if (is_null($token) || is_null($decoded)) {
    HTTPStatus::setStatus(401);
    $response = [
        "status" => false,
        "msg" => "No autorizado"
    ];
    echo json_encode($response);
    exit();
}

switch ($method) {
    case 'GET':
        switch ($path) {
            case 'gethistory':
                // Obtiene el historial médico completo de un usuario por ID
                if (in_array('get_medical_history', $permissionsArray) || in_array('get_client', $permissionsArray)) {
                    $userId = $router->getParam();
                    
                    if (empty($userId)) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de usuario requerido"
                        ];
                    } else {
                        $data = $instance->getMedicalHistoryByUserId($userId);
                        
                        if ($data['patient_info']) {
                            HTTPStatus::setStatus(200);
                            $response = [
                                "status" => "success",
                                "msg" => "Historial médico obtenido correctamente",
                                "data" => $data
                            ];
                        } else {
                            HTTPStatus::setStatus(404);
                            $response = [
                                "status" => false,
                                "msg" => "Usuario no encontrado"
                            ];
                        }
                    }
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => "No autorizado"
                    ];
                }
                echo json_encode($response);
                break;

            case 'getsummary':
                // Obtiene el resumen del historial médico (estadísticas)
                if (in_array('get_medical_history', $permissionsArray) || in_array('get_client', $permissionsArray)) {
                    $userId = $router->getParam();
                    
                    if (empty($userId)) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de usuario requerido"
                        ];
                    } else {
                        $data = $instance->getMedicalHistorySummary($userId);
                        
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => "Resumen del historial médico obtenido correctamente",
                            "data" => $data
                        ];
                    }
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" => "No autorizado"
                    ];
                }
                echo json_encode($response);
                break;

            case 'getmyhistory':
                // Permite a un usuario obtener su propio historial médico
                $userId = $decoded->id; // ID del usuario autenticado
                
                $data = $instance->getMedicalHistoryByUserId($userId);
                
                if ($data['patient_info']) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => "Tu historial médico obtenido correctamente",
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(404);
                    $response = [
                        "status" => false,
                        "msg" => "Historial médico no encontrado"
                    ];
                }
                echo json_encode($response);
                break;

            case 'getmysummary':
                // Permite a un usuario obtener el resumen de su propio historial médico
                $userId = $decoded->id; // ID del usuario autenticado
                
                $data = $instance->getMedicalHistorySummary($userId);
                
                HTTPStatus::setStatus(200);
                $response = [
                    "status" => "success",
                    "msg" => "Resumen de tu historial médico obtenido correctamente",
                    "data" => $data
                ];
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