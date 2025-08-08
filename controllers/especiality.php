<?php
use Classes\Especiality;
use Classes\Controller;
use Classes\Auth;
use Classes\Permission;
use Classes\HTTPStatus;

$controller = new Controller();
$instance = new Especiality();
$name_table = "especialities";
$auth = new Auth();
$permissions = new Permission();

/* $token = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : null;
$decoded = !is_null($token) ? $auth->verifyToken($token) : null;

if (!is_null($token) && !is_bool($permissions) && !is_null($decoded)) {
    $permissions = $auth->searchPermissions($decoded->permissions, "especiality");
} else {
    HTTPStatus::setStatus(401);
    $message = HTTPStatus::getMessage(401);
    $response = array(
        "status" => false,
        "msg" => $message
    );
    echo json_encode($response);
    die();
} */

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $controller->get($instance, $name_table);
    break;

    case 'POST':
        $controller->post($instance, $name_table, $body);
    break;

    case 'PUT':
        $controller->put($instance, $name_table, $body);
    break;

    case 'DELETE':
        $data = $controller->delete($instance, $name_table);
    break;

    default:
        echo ("404 METHOD NOT FOUND");
    break;
}