<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Ajustar límite de memoria para procesamiento de imágenes
@ini_set('memory_limit', '256M');

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Habilitar reporte de errores si se especifica APP_DEBUG=true en .env
if (isset($_ENV['APP_DEBUG']) && filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require 'funciones.php';
require 'database.php';

if (file_exists(__DIR__ . '/mail.php')) {
    require 'mail.php';
} else {
    require 'mail.example.php';
}

use Model\ActiveRecord;
ActiveRecord::setDB($db);
