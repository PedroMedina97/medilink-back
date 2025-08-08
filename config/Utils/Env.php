<?php

namespace Utils;

use Dotenv\Dotenv;
class Env
{
    public $server = 'http://localhost:4445';


    public static function generateKey()
    { // Carga el archivo .env
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $word = $_ENV['API_JWT'];
        $key = $word;
        return $key;
    }
}
