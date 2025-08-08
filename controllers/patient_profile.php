<?php
include_once './includes/headers.php';

use Classes\Auth;
use Classes\PatientProfile;
use Classes\HTTPStatus;

$instance = new PatientProfile();
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

// Verificar si la ruta requiere autenticación
if ($path !== 'public') {
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
            case 'getbyuserid':
                // Obtener perfil de salud de un usuario específico
                if (in_array('get_client', $permissionsArray) || in_array('get_medical_history', $permissionsArray)) {
                    $userId = $router->getParam();
                    $data = $instance->getProfileByUserId($userId);
                    
                    if ($data['success']) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => "Perfil de salud obtenido correctamente",
                            "data" => $data['data']
                        ];
                    } else {
                        HTTPStatus::setStatus(404);
                        $response = [
                            "status" => false,
                            "msg" => $data['message']
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

            case 'my-profile':
                // Obtener mi propio perfil de salud
                $userId = $decoded->id;
                $data = $instance->getProfileByUserId($userId);
                
                if ($data['success']) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => "Tu perfil de salud obtenido correctamente",
                        "data" => $data['data']
                    ];
                } else {
                    HTTPStatus::setStatus(404);
                    $response = [
                        "status" => false,
                        "msg" => $data['message']
                    ];
                }
                echo json_encode($response);
                break;

            case 'getbydoctor':
                // Obtener perfiles de todos los pacientes de un doctor
                if (in_array('get_client', $permissionsArray)) {
                    $doctorId = $router->getParam() ?: $decoded->id;
                    $data = $instance->getProfilesByDoctor($doctorId);
                    
                    if ($data['success']) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => "Perfiles de pacientes obtenidos correctamente",
                            "data" => $data['data'],
                            "total" => $data['total']
                        ];
                    } else {
                        HTTPStatus::setStatus(404);
                        $response = [
                            "status" => false,
                            "msg" => $data['message']
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

            case 'my-patients-profiles':
                // Obtener perfiles de mis pacientes (doctor autenticado)
                if (in_array('get_client', $permissionsArray)) {
                    $doctorId = $decoded->id;
                    $data = $instance->getProfilesByDoctor($doctorId);
                    
                    if ($data['success']) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => "Perfiles de tus pacientes obtenidos correctamente",
                            "data" => $data['data'],
                            "total" => $data['total']
                        ];
                    } else {
                        HTTPStatus::setStatus(404);
                        $response = [
                            "status" => false,
                            "msg" => $data['message']
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
            case 'create':
                // Crear perfil de salud para un usuario específico
                if (in_array('create_client', $permissionsArray) || in_array('update_client', $permissionsArray)) {
                    $userId = $body['user_id'] ?? null;
                    
                    if (!$userId) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de usuario requerido"
                        ];
                        echo json_encode($response);
                        break;
                    }

                    // Validar datos
                    $validation = $instance->validateProfileData($body);
                    if (!$validation['valid']) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "Datos inválidos",
                            "errors" => $validation['errors']
                        ];
                        echo json_encode($response);
                        break;
                    }

                    $data = $instance->createOrUpdateProfile($userId, $body);
                    
                    if ($data['success']) {
                        HTTPStatus::setStatus(201);
                        $response = [
                            "status" => "success",
                            "msg" => $data['message'],
                            "data" => $data['data']
                        ];
                    } else {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => $data['message']
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

            case 'my-profile':
                // Crear/actualizar mi propio perfil de salud
                $userId = $decoded->id;
                
                // Validar datos
                $validation = $instance->validateProfileData($body);
                if (!$validation['valid']) {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => false,
                        "msg" => "Datos inválidos",
                        "errors" => $validation['errors']
                    ];
                    echo json_encode($response);
                    break;
                }

                $data = $instance->createOrUpdateProfile($userId, $body);
                
                if ($data['success']) {
                    HTTPStatus::setStatus(201);
                    $response = [
                        "status" => "success",
                        "msg" => $data['message'],
                        "data" => $data['data']
                    ];
                } else {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => false,
                        "msg" => $data['message']
                    ];
                }
                echo json_encode($response);
                break;

            default:
                HTTPStatus::setStatus(404);
                $response = [
                    "status" => false,
                    "msg" => "Método no definido para esta clase"
                ];
                echo json_encode($response);
                break;
        }
        break;

    case 'PUT':
        switch ($path) {
            case 'update':
                // Actualizar perfil de salud de un usuario específico
                if (in_array('update_client', $permissionsArray)) {
                    $userId = $body['user_id'] ?? null;
                    
                    if (!$userId) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de usuario requerido"
                        ];
                        echo json_encode($response);
                        break;
                    }

                    // Validar datos
                    $validation = $instance->validateProfileData($body);
                    if (!$validation['valid']) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "Datos inválidos",
                            "errors" => $validation['errors']
                        ];
                        echo json_encode($response);
                        break;
                    }

                    $data = $instance->createOrUpdateProfile($userId, $body);
                    
                    if ($data['success']) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => $data['message'],
                            "data" => $data['data']
                        ];
                    } else {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => $data['message']
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

            case 'my-profile':
                // Actualizar mi propio perfil de salud
                $userId = $decoded->id;
                
                // Validar datos
                $validation = $instance->validateProfileData($body);
                if (!$validation['valid']) {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => false,
                        "msg" => "Datos inválidos",
                        "errors" => $validation['errors']
                    ];
                    echo json_encode($response);
                    break;
                }

                $data = $instance->createOrUpdateProfile($userId, $body);
                
                if ($data['success']) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => $data['message'],
                        "data" => $data['data']
                    ];
                } else {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => false,
                        "msg" => $data['message']
                    ];
                }
                echo json_encode($response);
                break;

            default:
                HTTPStatus::setStatus(404);
                $response = [
                    "status" => false,
                    "msg" => "Método no definido para esta clase"
                ];
                echo json_encode($response);
                break;
        }
        break;

    case 'DELETE':
        switch ($path) {
            case 'delete':
                // Eliminar perfil de salud de un usuario específico
                if (in_array('delete_client', $permissionsArray)) {
                    $userId = $router->getParam();
                    $data = $instance->deleteProfile($userId);
                    
                    if ($data['success']) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => $data['message']
                        ];
                    } else {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => $data['message']
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

            case 'my-profile':
                // Eliminar mi propio perfil de salud
                $userId = $decoded->id;
                $data = $instance->deleteProfile($userId);
                
                if ($data['success']) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => $data['message']
                    ];
                } else {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => false,
                        "msg" => $data['message']
                    ];
                }
                echo json_encode($response);
                break;

            default:
                HTTPStatus::setStatus(404);
                $response = [
                    "status" => false,
                    "msg" => "Método no definido para esta clase"
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