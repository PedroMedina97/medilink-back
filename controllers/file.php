<?php

use Classes\Controller;
use Classes\Auth;
use Classes\HTTPStatus;
use Classes\Order;

$controller = new Controller();
$auth = new Auth();
$response = null;
$function = $_SERVER['REQUEST_METHOD'];
$method = $router->getMethod();    // "getfile"
$action = $router->getParam();     // "download", "read", "list"
$code = $router->getExtra();       // nombre del archivo PDF
$order = new Order();

switch ($function) {
    case 'GET':
        switch ($method) {
            case 'getfile':
                $directory = 'docs/';

                // Validar existencia del directorio
                if (!is_dir($directory)) {
                    echo json_encode(["error" => "La carpeta no existe"]);
                    exit;
                }

                // Acción: listar archivos
                if ($action === 'list') {
                    $files = scandir($directory);
                    $files = array_diff($files, ['.', '..']);
                    echo json_encode(["files" => array_values($files)]);
                    exit;
                }

                // Sanitizar el código recibido como nombre del archivo
                $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $code);

                // Si no se especificó código
                if (empty($filename)) {
                    echo json_encode(["error" => "No se especificó un archivo"]);
                    exit;
                }

                $filePath = $directory . $filename;

                // Acción: leer contenido del archivo
                if ($action === 'read') {
                    if (file_exists($filePath)) {
                        echo json_encode(["content" => file_get_contents($filePath)]);
                    } else {
                        echo json_encode(["error" => "El archivo no existe"]);
                    }
                    exit;
                }

                // Acción: descargar el archivo
                if ($action === 'download') {
                    $folio = preg_replace('/^docs\/|\.pdf$/', '', $filePath);
                    $order->generateDocument($folio);

                    if (file_exists($filePath)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/pdf');
                        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($filePath));
                        readfile($filePath);
                        exit;
                    } else {
                        echo json_encode(["error" => "El archivo no existe"]);
                    }
                    exit;
                }

                if ($action === 'downloadbyid') {
                    $id = preg_replace('/^docs\/|\.pdf$/', '', $filePath);
                    $order->generateDocumentById($id);

                    if (file_exists($filePath)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/pdf');
                        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($filePath));
                        readfile($filePath);
                        exit;
                    } else {
                        echo json_encode(["error" => "El archivo no existe"]);
                    }
                    exit;
                }

                echo json_encode(["error" => "Acción no válida"]);
                exit;

            default:
                HTTPStatus::setStatus(404);
                echo json_encode([
                    "status" => false,
                    "msg" => HTTPStatus::getMessage(404)
                ]);
                break;
        }
        break;

    default:
        HTTPStatus::setStatus(405);
        echo json_encode([
            "status" => false,
            "msg" => HTTPStatus::getMessage(405)
        ]);
        break;
}
