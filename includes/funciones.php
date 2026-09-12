<?php

// Definir la ruta de la carpeta de imágenes (compatible con localhost, hosting y CLI)
if(!defined('CARPETA_IMAGENES')) {
    if(is_dir(__DIR__ . '/../public/imagenes/')) {
        define('CARPETA_IMAGENES', __DIR__ . '/../public/imagenes/');
    } elseif(isset($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'] . '/imagenes/')) {
        define('CARPETA_IMAGENES', $_SERVER['DOCUMENT_ROOT'] . '/imagenes/');
    } else {
        define('CARPETA_IMAGENES', __DIR__ . '/../public/imagenes/');
    }
}

function debuguear(mixed $variable) : void {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

function s(?string $html) : string {
    return htmlspecialchars($html ?? '');
}

function isAuth() : void {
    if(!isset($_SESSION['login'])) {
        header('Location: /login');
        exit;
    }
}

function isAdmin() : void {
    isAuth();
    if(($_SESSION['rol'] ?? '') !== 'admin') {
        header('Location: /');
        exit;
    }
}

function isEntrenador() : void {
    isAuth();
    if(($_SESSION['rol'] ?? '') !== 'entrenador') {
        header('Location: /');
        exit;
    }
}

function isAdminOEntrenador() : void {
    isAuth();
    $rol = $_SESSION['rol'] ?? '';
    if($rol !== 'admin' && $rol !== 'entrenador') {
        header('Location: /');
        exit;
    }
}


function isCliente() : void {
    isAuth();
    if(($_SESSION['rol'] ?? '') !== 'cliente') {
        header('Location: /');
        exit;
    }
}

function obtenerMensaje($codigo) {
    $mensaje = '';

    switch($codigo) {
        case 1:
            $mensaje = "Creado Correctamente";
            break;
        case 2:
            $mensaje = "Actualizado Correctamente";
            break;
        case 3:
            $mensaje = "Eliminado Correctamente";
            break;
        default:
            $mensaje = false;
            break;
    }

    return $mensaje;
}

function nombreDia(int $dia) : string {
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return $dias[$dia] ?? '';
}

/**
 * Retorna la configuración global del sitio (singleton en memoria)
 */
function obtenerConfiguracion(): \Model\Configuracion {
    static $config = null;
    if($config === null) {
        $registro = \Model\Configuracion::get(1);
        if($registro instanceof \Model\Configuracion) {
            $config = $registro;
        } else {
            $config = new \Model\Configuracion([
                'id' => 1,
                'nombre' => 'GYM',
                'logo' => '',
                'portada' => '',
                'email' => 'admin@gym.com',
                'whatsapp' => '',
                'instagram' => '',
                'facebook' => '',
                'tiktok' => '',
                'direccion' => 'Av. del Entrenamiento 123, Buenos Aires',
                'mapa_url' => ''
            ]);
        }
    }
    return $config;
}

