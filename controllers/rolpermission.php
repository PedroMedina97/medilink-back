<?php
use Classes\Controller;
use Classes\Auth;
use Classes\HTTPStatus;
use Classes\RolPermission;

$controller = new Controller();
$instance = new RolPermission();
$auth = new Auth();
$name_table = "rols_permissions";
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
            case 'getpermissionsbyrol':
                if (in_array('get_rolpermission', $permissionsArray)) {
                    $id = $router->getParam();
                    $data = $instance->getPermissionsbyIdRol($id);
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
            case 'create':
                if(in_array('create_rolpermission', $permissionsArray)){
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
            case 'updatepermissions':
                if (in_array('update_rolpermission', $permissionsArray)) {
                    $body = json_decode(file_get_contents("php://input"), true);
            
                    if (!isset($body['id_rol']) || !isset($body['permissions'])) {
                        HTTPStatus::setStatus(400);
                        $response = [
                            "status" => false,
                            "msg" => "Datos incompletos"
                        ];
                        echo json_encode($response);
                        exit();
                    }
            
                    $id_rol = $body['id_rol'];
                    $permissions = $body['permissions']; // Lista de permisos seleccionados [{id_permission: "1", id_rol: "2"}, ...]
            
                    try {
                        // 1️⃣ Eliminar permisos actuales del rol
                        $instance->deletePermissionsByRol($id_rol);
            
                        // 2️⃣ Insertar los nuevos permisos seleccionados
                        foreach ($permissions as $perm) {
                            $instance->addPermissionToRol($perm['id_permission'], $id_rol);
                        }
            
                        HTTPStatus::setStatus(200);
                        $response = [
                            "status" => "success",
                            "msg" => "Permisos actualizados correctamente",
                            "data" => $permissions
                        ];
                    } catch (Exception $e) {
                        HTTPStatus::setStatus(500);
                        $response = [
                            "status" => false,
                            "msg" => "Error en la base de datos",
                            "error" => $e->getMessage()
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
            
            default:
                echo "Método no definido para esta clase";
                break;
        }
        break;

    case 'DELETE':
        switch($path){
            case 'delete':
                if(in_array('delete_rolpermission', $permissionsArray)){
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