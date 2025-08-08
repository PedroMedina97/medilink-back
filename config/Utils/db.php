<?php

use Dotenv\Dotenv;

// Carga el archivo .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

setlocale(LC_TIME, 'es_ES');

 $usuario = $_ENV['DB_USER'];
 $base = $_ENV['DB_NAME'];
 $contrasena = $_ENV['DB_PASSWORD'];
 $dbhost = $_ENV['DB_HOST'];
 
global $db;
$db = new mysqli($dbhost, $usuario, $contrasena, $base) or die("Error al conectar con la base de datos");

mysqli_set_charset($db, 'utf8');
date_default_timezone_set("America/Mexico_City");