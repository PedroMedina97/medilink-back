<?php
include_once './includes/headers.php';

use Classes\Auth;
use Classes\User;
use Classes\Controller;
use Classes\HTTPStatus;
use Classes\MedicalHistory;

$controller = new Controller();
$instance = new User();
$medicalHistory = new MedicalHistory();
$auth = new Auth();
$name_table = "users";
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
                if (in_array('getall_user', $permissionsArray)) {
                    $data = $instance->getAll('users');
                    $response = [
                        "status" => "success",
                        "msg" => $data ? "Fila(s) o Elemento(s) encontrada(s)" : "Fila(s) o Elemento(s) no encontrada(s)",
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
                if (in_array('get_user', $permissionsArray)) {
                    $id = $router->getParam();
                    $data = $instance->getById('users', $id);
                    $response = [
                        "status" => "success",
                        "msg" => $data ? "Fila(s) o Elemento(s) encontrada(s)" : "Fila(s) o Elemento(s) no encontrada(s)",
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
            case 'getinstance':
                $id = $router->getParam();
                $data = $instance->getById('users', $id);
                $response = [
                    "status" => "success",
                    "msg" => $data ? "Fila(s) o Elemento(s) encontrada(s)" : "Fila(s) o Elemento(s) no encontrada(s)",
                    "data" => $data
                ];

                echo json_encode($response);
                break;
            case 'getbyidrol':
                if (in_array('get_user', $permissionsArray)) {
                    $id = $router->getParam();
                    $data = $instance->getUsersByRol($id);
                    $response = [
                        "status" => "success",
                        "msg" => $data ? "Fila(s) o Elemento(s) encontrada(s)" : "Fila(s) o Elemento(s) no encontrada(s)",
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
            case 'getmypatients':
                if (in_array('get_client', $permissionsArray)) {
                    $id = $router->getParam();
                    $data = $instance->getByParentId('users', 'parent_id', $id);
                    if ($data) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => HTTPStatus::getMessage(200),
                            "data" => $data
                        ];
                    } else {
                        HTTPStatus::setStatus(204);
                        $response = [
                            "status" => false,
                            "msg" => HTTPStatus::getMessage(204)
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

            case 'medical-history':
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
                        $data = $medicalHistory->getMedicalHistoryByUserId($userId);
                        
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

            case 'medical-summary':
                // Obtiene el resumen del historial médico de un usuario por ID
                if (in_array('get_medical_history', $permissionsArray) || in_array('get_client', $permissionsArray)) {
                    $userId = $router->getParam();
                    
                    if (empty($userId)) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de usuario requerido"
                        ];
                    } else {
                        $data = $medicalHistory->getMedicalHistorySummary($userId);
                        
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

            case 'my-medical-history':
                // Permite a un usuario obtener su propio historial médico
                $userId = $decoded->id; // ID del usuario autenticado
                
                $data = $medicalHistory->getMedicalHistoryByUserId($userId);
                
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

            case 'my-medical-summary':
                // Permite a un usuario obtener el resumen de su propio historial médico
                $userId = $decoded->id; // ID del usuario autenticado
                
                $data = $medicalHistory->getMedicalHistorySummary($userId);
                
                HTTPStatus::setStatus(200);
                $response = [
                    "status" => "success",
                    "msg" => "Resumen de tu historial médico obtenido correctamente",
                    "data" => $data
                ];
                echo json_encode($response);
                break;

            case 'get-logo':
                // Obtener logo del usuario
                $userId = $router->getParam();
                if ($userId) {
                    $data = $instance->getUserLogo($userId);
                    if ($data['success']) {
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => "Logo obtenido correctamente",
                            "data" => $data
                        ];
                    } else {
                        HTTPStatus::setStatus(404);
                        $response = [
                            "status" => false,
                            "msg" => $data['message']
                        ];
                    }
                } else {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => false,
                        "msg" => "ID de usuario requerido"
                    ];
                }
                echo json_encode($response);
                break;

            case 'my-logo':
                // Obtener el logo del usuario autenticado
                $userId = $decoded->id;
                $data = $instance->getUserLogo($userId);
                if ($data['success']) {
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => "Logo obtenido correctamente",
                        "data" => $data
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

            case 'doctor-statistics':
                // Obtener estadísticas del doctor basadas en cortes de caja
                if (in_array('get_user', $permissionsArray) || in_array('get_cashcut', $permissionsArray)) {
                    $doctorId = $router->getParam();
                    
                    if (empty($doctorId)) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de doctor requerido"
                        ];
                    } else {
                        $data = $instance->getDoctorStatisticsByCashCuts($doctorId);
                        
                        if ($data['success']) {
                            HTTPStatus::setStatus(200);
                            $response = [
                                "status" => "success",
                                "msg" => "Estadísticas del doctor obtenidas correctamente",
                                "data" => $data
                            ];
                        } else {
                            HTTPStatus::setStatus(404);
                            $response = [
                                "status" => false,
                                "msg" => $data['message']
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

            case 'doctor-top-service':
                // Obtener servicio más solicitado del mes por doctor
                if (in_array('get_user', $permissionsArray) || in_array('getall_appointment', $permissionsArray)) {
                    $params = explode('/', $router->getParam());
                    $doctorId = $params[0] ?? null;
                    $month = $params[1] ?? null; // Opcional, formato YYYY-MM
                    
                    if (empty($doctorId)) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de doctor requerido"
                        ];
                    } else {
                        $data = $instance->getMostRequestedServiceByDoctor($doctorId, $month);
                        
                        if ($data['success']) {
                            HTTPStatus::setStatus(200);
                            $response = [
                                "status" => "success",
                                "msg" => "Servicio más solicitado obtenido correctamente",
                                "data" => $data
                            ];
                        } else {
                            HTTPStatus::setStatus(400);
                            $response = [
                                "status" => false,
                                "msg" => $data['message']
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

            case 'doctor-earnings-by-subsidiary':
                // Obtener ganancias por sucursal de un doctor
                if (in_array('get_user', $permissionsArray) || in_array('get_cashcut', $permissionsArray)) {
                    $doctorId = $router->getParam();
                    
                    if (empty($doctorId)) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de doctor requerido"
                        ];
                    } else {
                        $data = $instance->getEarningsBySubsidiaryByDoctor($doctorId);
                        
                        if ($data['success']) {
                            HTTPStatus::setStatus(200);
                            $response = [
                                "status" => "success",
                                "msg" => "Ganancias por sucursal obtenidas correctamente",
                                "data" => $data
                            ];
                        } else {
                            HTTPStatus::setStatus(404);
                            $response = [
                                "status" => false,
                                "msg" => $data['message']
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

            case 'doctor-monthly-income':
                // Obtener gráfica de ingresos mensuales por doctor
                if (in_array('get_user', $permissionsArray) || in_array('get_cashcut', $permissionsArray)) {
                    $params = explode('/', $router->getParam());
                    $doctorId = $params[0] ?? null;
                    $month = $params[1] ?? null; // Requerido, formato YYYY-MM
                    
                    if (empty($doctorId) || empty($month)) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de doctor y mes requeridos (formato: doctor_id/YYYY-MM)"
                        ];
                    } else {
                        $data = $instance->getMonthlyIncomeByDoctor($doctorId, $month);
                        
                        if ($data['success']) {
                            HTTPStatus::setStatus(200);
                            $response = [
                                "status" => "success",
                                "msg" => "Ingresos mensuales obtenidos correctamente",
                                "data" => $data
                            ];
                        } else {
                            HTTPStatus::setStatus(400);
                            $response = [
                                "status" => false,
                                "msg" => $data['message']
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

            case 'doctor-weekly-income':
                // Obtener gráfica de ingresos de la semana por doctor
                if (in_array('get_user', $permissionsArray) || in_array('get_cashcut', $permissionsArray)) {
                    $doctorId = $router->getParam();
                    
                    if (empty($doctorId)) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "ID de doctor requerido"
                        ];
                    } else {
                        $data = $instance->getWeeklyIncomeByDoctor($doctorId);
                        
                        if ($data['success']) {
                            HTTPStatus::setStatus(200);
                            $response = [
                                "status" => "success",
                                "msg" => "Ingresos semanales obtenidos correctamente",
                                "data" => $data
                            ];
                        } else {
                            HTTPStatus::setStatus(404);
                            $response = [
                                "status" => false,
                                "msg" => $data['message']
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
            case 'login':
                $email = $body['email'];
                $password = $body['password'];
                $data = $instance->login($email, $password);
                if ($data) {
                    $response = [
                        "status" => "success",
                        "email" => $email,
                        "token" => $data['token']
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => "false",
                        "msg" => "Credenciales no válidas"
                    ];
                }
                echo json_encode($response);
                break;

            case 'register':
                if (in_array('create_user', $permissionsArray)) {
                    $parentId = $body['parentId'];
                    $name = $body['name'];
                    $lastname = $body['lastname'];
                    $email = $body['email'];
                    $password = $body['password'];
                    $birthday = $body['birthday'];
                    $phone = $body['phone'];
                    $related = $body['related'];
                    $address = $body['address'];
                    $id_rol = $body['id_rol'];
                    $data = $instance->insertUser($parentId, $name, $lastname, $email, $password, $birthday, $phone, $address, $id_rol, $related);

                    if ($data) {
                        HTTPStatus::setStatus(201);
                        $response = [
                            "status" => "success",
                            "data" => $data,
                            "msg" => HTTPStatus::getMessage(201)
                        ];
                    } else {
                        HTTPStatus::setStatus(403);
                        $response = [
                            "status" => "false",
                            "data" => $data,
                            "msg" => HTTPStatus::getMessage(403)
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
            case 'register-patient':
                if (in_array('create_client', $permissionsArray)) {
                    $parentId = $body['parentId'];
                    $name = $body['name'];
                    $lastname = $body['lastname'];
                    $email = $body['email'];
                    $password = $body['password'];
                    $birthday = $body['birthday'];
                    $phone = $body['phone'];
                    $related = $body['related'];
                    $address = $body['address'];
                    $id_rol = $body['id_rol'];
                    $data = $instance->insertUser($parentId, $name, $lastname, $email, $password, $birthday, $phone, $address, $id_rol, $related);

                    if ($data) {
                        HTTPStatus::setStatus(201);
                        $response = [
                            "status" => "success",
                            "data" => $data,
                            "msg" => HTTPStatus::getMessage(201)
                        ];
                    } else {
                        HTTPStatus::setStatus(403);
                        $response = [
                            "status" => "false",
                            "data" => $data,
                            "msg" => HTTPStatus::getMessage(403)
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

            case 'upload-logo':
                // Subir logo para el usuario autenticado
                $userId = $decoded->id;
                
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $data = $instance->uploadUserLogo($userId, $_FILES['logo']);
                    
                    if ($data['success']) {
                        HTTPStatus::setStatus(201);
                        $response = [
                            "status" => "success",
                            "msg" => $data['message'],
                            "data" => [
                                "logo_path" => $data['logo_path']
                            ]
                        ];
                    } else {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => $data['message']
                        ];
                    }
                } else {
                    HTTPStatus::setStatus(400);
                    $response = [
                        "status" => false,
                        "msg" => "No se recibió un archivo válido"
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
            case 'updateuser':
                $id = $router->getParam();
                if (in_array('update_user', $permissionsArray)) {
                    $data = $controller->update($instance, $name_table, $body);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => $data ? HTTPStatus::getMessage(200) : HTTPStatus::getMessage(400),
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
            case 'updateclient':
                $id = $router->getParam();
                if (in_array('update_client', $permissionsArray)) {
                    $data = $controller->update($instance, $name_table, $body);
                    HTTPStatus::setStatus(200);
                    $response = [
                        "status" => "success",
                        "msg" => $data ? HTTPStatus::getMessage(200) : HTTPStatus::getMessage(400),
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
            case 'deleteuser':
                if (in_array('delete_user', $permissionsArray)) {
                    $data = $controller->delete($instance, $name_table);
                    $response = [
                        "status" => "success",
                        "msg" => $data ? "Fila(s) o Elemento(s) eliminado(s)" : "Fila(s) o Elemento(s) no eliminado(s)",
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" =>  HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
            break;
            case 'deleteclient':
                if (in_array('delete_client', $permissionsArray)) {
                    $data = $controller->delete($instance, $name_table);
                    $response = [
                        "status" => "success",
                        "msg" => $data ? "Fila(s) o Elemento(s) eliminado(s)" : "Fila(s) o Elemento(s) no eliminado(s)",
                        "data" => $data
                    ];
                } else {
                    HTTPStatus::setStatus(401);
                    $response = [
                        "status" => false,
                        "msg" =>  HTTPStatus::getMessage(401)
                    ];
                }
                echo json_encode($response);
            break;

            case 'delete-logo':
                // Eliminar logo del usuario autenticado
                $userId = $decoded->id;
                $data = $instance->deleteUserLogo($userId);
                
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
