<?php

namespace Controllers;

use MVC\Router;
use Model\Plan;
use Model\ActiveRecord;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager as Image;

class PlanController {
    public static function index(Router $router) {
        isAdmin();

        $planes = Plan::all();
        $resultado = $_GET['resultado'] ?? null;
        $error = $_GET['error'] ?? null;

        $router->render('admin/planes/index', [
            'planes' => $planes,
            'resultado' => $resultado,
            'error' => $error,
            'nombre' => $_SESSION['nombre'] ?? '',
            'tipo' => 'dashboard'
        ]);
    }

    public static function crear(Router $router) {
        isAdmin();

        $plan = new Plan;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plan->sincronizar($_POST['plan'] ?? []);
            $plan->activo = isset($_POST['plan']['activo']) ? 1 : 0;
            $plan->cantidad_clases = !empty($_POST['plan']['cantidad_clases']) ? (int)$_POST['plan']['cantidad_clases'] : null;

            $nombreImagen = md5(uniqid((string)rand(), true)) . ".webp";
            $imagen = null;

            if(!empty($_FILES['plan']['tmp_name']['imagen']) && is_uploaded_file($_FILES['plan']['tmp_name']['imagen'])) {
                try {
                    $manager = new Image(Driver::class);
                    $contenido = file_get_contents($_FILES['plan']['tmp_name']['imagen']);
                    $imagen = $manager->decode($contenido)->cover(800, 600);
                } catch (\Throwable $e) {
                    Plan::setAlerta('error', 'Error al procesar la imagen: ' . $e->getMessage());
                }
            }

            $alertas = array_merge(Plan::getAlertas(), $plan->validarPlan());

            if(empty($alertas)) {
                try {
                    if($imagen) {
                        if(!is_dir(CARPETA_IMAGENES)) {
                            mkdir(CARPETA_IMAGENES, 0777, true);
                        }
                        $imagen->save(CARPETA_IMAGENES . $nombreImagen);
                        $plan->setImagen($nombreImagen);
                    }

                    $resultado = $plan->guardar();

                    if($resultado) {
                        header('Location: /admin/planes?resultado=1');
                        return;
                    }
                } catch (\Throwable $e) {
                    Plan::setAlerta('error', 'Error al guardar la imagen en el servidor: ' . $e->getMessage());
                    $alertas = Plan::getAlertas();
                }
            }
        }

        $router->render('admin/planes/crear', [
            'plan' => $plan,
            'alertas' => $alertas,
            'nombre' => $_SESSION['nombre'] ?? '',
            'tipo' => 'dashboard'
        ]);
    }

    public static function actualizar(Router $router) {
        isAdmin();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /admin/planes');
            return;
        }

        $plan = Plan::find($id);

        if(!$plan) {
            header('Location: /admin/planes');
            return;
        }

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plan->sincronizar($_POST['plan'] ?? []);
            $plan->activo = isset($_POST['plan']['activo']) ? 1 : 0;
            $plan->cantidad_clases = !empty($_POST['plan']['cantidad_clases']) ? (int)$_POST['plan']['cantidad_clases'] : null;

            $nombreImagen = md5(uniqid((string)rand(), true)) . ".webp";
            $imagen = null;

            if(!empty($_FILES['plan']['tmp_name']['imagen']) && is_uploaded_file($_FILES['plan']['tmp_name']['imagen'])) {
                try {
                    $manager = new Image(Driver::class);
                    $contenido = file_get_contents($_FILES['plan']['tmp_name']['imagen']);
                    $imagen = $manager->decode($contenido)->cover(800, 600);
                } catch (\Throwable $e) {
                    Plan::setAlerta('error', 'Error al procesar la imagen: ' . $e->getMessage());
                }
            }

            $alertas = array_merge(Plan::getAlertas(), $plan->validarPlan());

            if(empty($alertas)) {
                try {
                    if($imagen) {
                        if(!is_dir(CARPETA_IMAGENES)) {
                            mkdir(CARPETA_IMAGENES, 0777, true);
                        }
                        $imagen->save(CARPETA_IMAGENES . $nombreImagen);
                        $plan->setImagen($nombreImagen);
                    }

                    $resultado = $plan->guardar();

                    if($resultado) {
                        header('Location: /admin/planes?resultado=2');
                        return;
                    }
                } catch (\Throwable $e) {
                    Plan::setAlerta('error', 'Error al guardar la imagen en el servidor: ' . $e->getMessage());
                    $alertas = Plan::getAlertas();
                }
            }
        }

        $router->render('admin/planes/actualizar', [
            'plan' => $plan,
            'alertas' => $alertas,
            'nombre' => $_SESSION['nombre'] ?? '',
            'tipo' => 'dashboard'
        ]);
    }

    public static function eliminar() {
        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if($id) {
                $plan = Plan::find($id);
                if($plan) {
                    // Verificar si el plan tiene membresías asociadas (activas o históricas)
                    $membresiasVinculadas = ActiveRecord::SQL("SELECT COUNT(*) AS total FROM membresias WHERE plan_id = {$id}");
                    $totalMembresias = (int)($membresiasVinculadas[0]->total ?? 0);

                    if($totalMembresias > 0) {
                        header('Location: /admin/planes?error=plan_con_membresias');
                        return;
                    }

                    try {
                        $resultado = $plan->eliminar();
                        if($resultado) {
                            $plan->eliminarImagen();
                            header('Location: /admin/planes?resultado=3');
                            return;
                        }
                    } catch (\Throwable $e) {
                        header('Location: /admin/planes?error=plan_con_membresias');
                        return;
                    }
                }
            }
        }

        header('Location: /admin/planes');
    }
}
