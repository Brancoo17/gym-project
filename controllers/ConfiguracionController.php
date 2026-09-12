<?php

namespace Controllers;

use MVC\Router;
use Model\Configuracion;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager as Image;

class ConfiguracionController {

    /**
     * Muestra el formulario de configuración general del sitio
     */
    public static function index(Router $router): void {
        isAdmin();

        $configuracion = obtenerConfiguracion();
        $resultado = $_GET['resultado'] ?? null;
        $alertas = [];

        $router->render('admin/configuracion/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'configuracion' => $configuracion,
            'alertas' => $alertas,
            'resultado' => $resultado,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Procesa y guarda los cambios de configuración del sitio
     */
    public static function guardar(Router $router): void {
        isAdmin();

        $configuracion = obtenerConfiguracion();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $configuracion->sincronizar($_POST['configuracion'] ?? []);

            $imgLogo = null;
            $nombreLogo = '';
            $imgPortada = null;
            $nombrePortada = '';
            $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];

            try {
                // Procesar subida de Logo
                if(!empty($_FILES['logo']['tmp_name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
                    $manager = new Image(Driver::class);
                    $contenido = file_get_contents($_FILES['logo']['tmp_name']);
                    $imgLogo = $manager->decode($contenido)->scaleDown(600, 600);
                    $nombreLogo = 'logo_' . md5(uniqid((string)rand(), true)) . '.webp';
                }

                // Procesar subida de Portada Hero
                if(!empty($_FILES['portada']['tmp_name']) && is_uploaded_file($_FILES['portada']['tmp_name'])) {
                    $manager = new Image(Driver::class);
                    $contenido = file_get_contents($_FILES['portada']['tmp_name']);
                    $imgPortada = $manager->decode($contenido)->scaleDown(1920, 1080);
                    $nombrePortada = 'portada_' . md5(uniqid((string)rand(), true)) . '.webp';
                }
            } catch (\Throwable $e) {
                Configuracion::setAlerta('error', 'Error al procesar la imagen: ' . $e->getMessage());
            }

            $alertas = array_merge(Configuracion::getAlertas(), $configuracion->validar());

            if(empty($alertas)) {
                try {
                    if(!is_dir(CARPETA_IMAGENES)) {
                        mkdir(CARPETA_IMAGENES, 0777, true);
                    }

                    if($imgLogo && $nombreLogo) {
                        $imgLogo->save(CARPETA_IMAGENES . $nombreLogo);
                        $configuracion->setLogo($nombreLogo);
                    }

                    if($imgPortada && $nombrePortada) {
                        $imgPortada->save(CARPETA_IMAGENES . $nombrePortada);
                        $configuracion->setPortada($nombrePortada);
                    }

                    $resultado = $configuracion->guardar();

                    if($resultado) {
                        header('Location: /admin/configuracion?resultado=2');
                        return;
                    }
                } catch (\Throwable $e) {
                    Configuracion::setAlerta('error', 'Error al guardar la imagen en el servidor: ' . $e->getMessage() . '. Verificá los permisos de la carpeta public/imagenes/.');
                    $alertas = Configuracion::getAlertas();
                }
            }
        }

        $router->render('admin/configuracion/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'configuracion' => $configuracion,
            'alertas' => $alertas,
            'resultado' => null,
            'tipo' => 'dashboard'
        ]);
    }
}
