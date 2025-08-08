<?php

namespace Classes;

class HTTPStatus {
    // Define constantes para los códigos de estado comunes
    const OK = 200;
    const CREATED = 201;
    const NO_CONTENT = 204;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const CONFLICT = 409;
    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVAILABLE = 503;
    const NOT_ACCEPTABLE = 406;

    // Método para establecer el código de estado y obtener su mensaje asociado
    public static function setStatus($statusCode) {
        http_response_code($statusCode);
    }

    // Método para obtener el mensaje asociado a un código de estado
    public static function getMessage($statusCode) {
        switch ($statusCode) {
            case self::OK:
                return 'OK';
            case self::CREATED:
                return 'Created';
            case self::NO_CONTENT:
                return 'No Content';
            case self::BAD_REQUEST:
                return 'Bad Request';
            case self::UNAUTHORIZED:
                return 'Unauthorized';
            case self::FORBIDDEN:
                return 'Forbidden';
            case self::NOT_FOUND:
                return 'Not Found';
            case self::METHOD_NOT_ALLOWED:
                return 'Method Not Allowed';
            case self::CONFLICT:
                return 'Conflict';
            case self::INTERNAL_SERVER_ERROR:
                return 'Internal Server Error';
            case self::SERVICE_UNAVAILABLE:
                return 'Service Unavailable';
            case self::NOT_ACCEPTABLE;
                return  'NOT ACCEPTABLE';
            default:
                return 'Unknown Status Code';
        }
    }
}

