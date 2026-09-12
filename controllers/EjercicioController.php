<?php

namespace Controllers;

use MVC\Router;
use Model\Ejercicio;
use Model\ActiveRecord;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager as Image;

class EjercicioController {

    /**
     * Listado del banco de ejercicios con buscador y filtros
     */
    public static function index(Router $router): void {
        isAuth();

        $rol = $_SESSION['rol'] ?? '';
        $puedeGestionar = ($rol === 'admin' || $rol === 'entrenador');

        $resultado = $_GET['resultado'] ?? null;
        $error = $_GET['error'] ?? null;
        $grupoFiltro = $_GET['grupo'] ?? '';

        if($grupoFiltro && in_array($grupoFiltro, Ejercicio::GRUPOS_MUSCULARES)) {
            $ejercicios = Ejercicio::whereAll('grupo_muscular', $grupoFiltro);
        } else {
            $ejercicios = Ejercicio::SQL("SELECT * FROM ejercicios ORDER BY grupo_muscular ASC, nombre ASC");
        }

        $router->render('admin/ejercicios/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'rol' => $rol,
            'puedeGestionar' => $puedeGestionar,
            'ejercicios' => $ejercicios,
            'resultado' => $resultado,
            'error' => $error,
            'grupos' => Ejercicio::GRUPOS_MUSCULARES,
            'grupoSeleccionado' => $grupoFiltro,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Formulario de creación de un nuevo ejercicio
     */
    public static function crear(Router $router): void {
        isAdminOEntrenador();

        $alertas = [];
        $ejercicio = new Ejercicio();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ejercicio->sincronizar($_POST['ejercicio'] ?? []);

            $nombreImagen = md5(uniqid((string)rand(), true)) . ".webp";
            $imagen = null;

            if(!empty($_FILES['ejercicio']['tmp_name']['imagen']) && is_uploaded_file($_FILES['ejercicio']['tmp_name']['imagen'])) {
                try {
                    $manager = new Image(Driver::class);
                    $contenido = file_get_contents($_FILES['ejercicio']['tmp_name']['imagen']);
                    $imagen = $manager->decode($contenido)->cover(800, 600);
                } catch (\Throwable $e) {
                    Ejercicio::setAlerta('error', 'Error al procesar la imagen: ' . $e->getMessage());
                }
            }

            $alertas = array_merge(Ejercicio::getAlertas(), $ejercicio->validarEjercicio());

            if(empty($alertas)) {
                try {
                    if($imagen) {
                        if(!is_dir(CARPETA_IMAGENES)) {
                            mkdir(CARPETA_IMAGENES, 0777, true);
                        }
                        $imagen->save(CARPETA_IMAGENES . $nombreImagen);
                        $ejercicio->setImagen($nombreImagen);
                    }

                    $resultado = $ejercicio->guardar();

                    if($resultado) {
                        header('Location: /admin/ejercicios?resultado=1');
                        return;
                    }
                } catch (\Throwable $e) {
                    Ejercicio::setAlerta('error', 'Error al guardar la imagen en el servidor: ' . $e->getMessage());
                    $alertas = Ejercicio::getAlertas();
                }
            }
        }

        $router->render('admin/ejercicios/crear', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'rol' => $_SESSION['rol'] ?? '',
            'ejercicio' => $ejercicio,
            'alertas' => $alertas,
            'grupos' => Ejercicio::GRUPOS_MUSCULARES,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Formulario de edición de un ejercicio existente
     */
    public static function actualizar(Router $router): void {
        isAdminOEntrenador();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$id) {
            header('Location: /admin/ejercicios');
            return;
        }

        $ejercicio = Ejercicio::find($id);
        if(!$ejercicio) {
            header('Location: /admin/ejercicios');
            return;
        }

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ejercicio->sincronizar($_POST['ejercicio'] ?? []);

            $nombreImagen = md5(uniqid((string)rand(), true)) . ".webp";
            $imagen = null;

            if(!empty($_FILES['ejercicio']['tmp_name']['imagen']) && is_uploaded_file($_FILES['ejercicio']['tmp_name']['imagen'])) {
                try {
                    $manager = new Image(Driver::class);
                    $contenido = file_get_contents($_FILES['ejercicio']['tmp_name']['imagen']);
                    $imagen = $manager->decode($contenido)->cover(800, 600);
                } catch (\Throwable $e) {
                    Ejercicio::setAlerta('error', 'Error al procesar la imagen: ' . $e->getMessage());
                }
            }

            $alertas = array_merge(Ejercicio::getAlertas(), $ejercicio->validarEjercicio());

            if(empty($alertas)) {
                try {
                    if($imagen) {
                        if(!is_dir(CARPETA_IMAGENES)) {
                            mkdir(CARPETA_IMAGENES, 0777, true);
                        }
                        $imagen->save(CARPETA_IMAGENES . $nombreImagen);
                        $ejercicio->setImagen($nombreImagen);
                    }

                    $resultado = $ejercicio->guardar();

                    if($resultado) {
                        header('Location: /admin/ejercicios?resultado=2');
                        return;
                    }
                } catch (\Throwable $e) {
                    Ejercicio::setAlerta('error', 'Error al guardar la imagen en el servidor: ' . $e->getMessage());
                    $alertas = Ejercicio::getAlertas();
                }
            }
        }

        $router->render('admin/ejercicios/actualizar', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'rol' => $_SESSION['rol'] ?? '',
            'ejercicio' => $ejercicio,
            'alertas' => $alertas,
            'grupos' => Ejercicio::GRUPOS_MUSCULARES,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Eliminación de un ejercicio
     */
    public static function eliminar(): void {
        isAdminOEntrenador();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if($id) {
                $ejercicio = Ejercicio::find($id);

                if($ejercicio) {
                    // Verificar si el ejercicio está asignado en alguna rutina existente
                    $rutinasVinculadas = ActiveRecord::SQL("SELECT COUNT(*) AS total FROM rutina_ejercicios WHERE ejercicio_id = {$id}");
                    $enUso = (int)($rutinasVinculadas[0]->total ?? 0);

                    if($enUso > 0) {
                        header('Location: /admin/ejercicios?error=ejercicio_en_rutina');
                        return;
                    }

                    try {
                        $resultado = $ejercicio->eliminar();
                        if($resultado) {
                            $ejercicio->eliminarImagen();
                            header('Location: /admin/ejercicios?resultado=3');
                            return;
                        }
                    } catch (\Throwable $e) {
                        header('Location: /admin/ejercicios?error=ejercicio_en_rutina');
                        return;
                    }
                }
            }
        }

        header('Location: /admin/ejercicios');
    }
}
