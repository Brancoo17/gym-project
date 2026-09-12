<?php

namespace Controllers;

use MVC\Router;
use Model\Rutina;
use Model\RutinaEjercicio;
use Model\Plan;
use Model\Usuario;
use Model\Ejercicio;
use Model\ActiveRecord;

class RutinaController {

    /**
     * Listado de rutinas con filtro por plan y fecha
     */
    public static function index(Router $router): void {
        isAdminOEntrenador();

        $resultado = $_GET['resultado'] ?? null;
        $planFiltro = filter_var($_GET['plan_id'] ?? null, FILTER_VALIDATE_INT);
        $fechaFiltro = filter_var($_GET['fecha'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);

        $condiciones = [];
        if($planFiltro) {
            $condiciones[] = "rutinas.plan_id = {$planFiltro}";
        }
        if($fechaFiltro) {
            $condiciones[] = "rutinas.fecha = '{$fechaFiltro}'";
        }

        // Si es entrenador, puede ver todas o sus propias rutinas
        $whereSql = !empty($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

        $rutinas = Rutina::SQL("
            SELECT rutinas.*, 
                   planes.nombre AS plan_nombre,
                   CONCAT(e.nombre, ' ', e.apellido) AS entrenador_nombre,
                   CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
                   c.email AS cliente_email,
                   COUNT(rutina_ejercicios.id) AS total_ejercicios,
                   COUNT(DISTINCT rutina_ejercicios.bloque) AS total_bloques
            FROM rutinas
            INNER JOIN planes ON planes.id = rutinas.plan_id
            INNER JOIN usuarios e ON e.id = rutinas.entrenador_id
            LEFT JOIN usuarios c ON c.id = rutinas.cliente_id
            LEFT JOIN rutina_ejercicios ON rutina_ejercicios.rutina_id = rutinas.id
            {$whereSql}
            GROUP BY rutinas.id
            ORDER BY rutinas.fecha DESC, rutinas.id DESC
        ");

        $planes = Plan::whereAll('activo', 1);

        $router->render('admin/rutinas/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'rol' => $_SESSION['rol'] ?? '',
            'rutinas' => $rutinas,
            'planes' => $planes,
            'planSeleccionado' => $planFiltro,
            'fechaSeleccionada' => $fechaFiltro,
            'resultado' => $resultado,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Constructor interactivo para crear una nueva rutina
     */
    public static function crear(Router $router): void {
        isAdminOEntrenador();

        $alertas = [];
        $rutina = new Rutina();
        
        // Si ingresa un entrenador, auto-seleccionar su ID
        if(($_SESSION['rol'] ?? '') === 'entrenador') {
            $rutina->entrenador_id = $_SESSION['id'];
        }

        if(!empty($_GET['cliente_id'])) {
            $rutina->cliente_id = (int)$_GET['cliente_id'];
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rutina->sincronizar($_POST['rutina'] ?? []);

            $esGrupal = !empty($_POST['check_rutina_grupal']);
            $clientesIds = $_POST['clientes_ids'] ?? [];

            // Fallback si vino por rutina[cliente_id]
            if(!$esGrupal && empty($clientesIds) && !empty($_POST['rutina']['cliente_id'])) {
                $clientesIds = [(int)$_POST['rutina']['cliente_id']];
            }

            // Filtrar IDs enteros válidos y eliminar duplicados
            $clientesIds = array_values(array_unique(array_filter(array_map('intval', (array)$clientesIds))));

            if($esGrupal) {
                $rutina->cliente_id = null;
                $clientesIds = [];
            } else {
                if(empty($clientesIds)) {
                    $alertas['error'][] = 'Debés seleccionar al menos un alumno para la rutina personalizada o marcar la opción de Rutina Grupal';
                } else {
                    $rutina->cliente_id = $clientesIds[0];
                }
            }

            // Validar que los alumnos seleccionados tengan membresía activa para este plan
            if(!$esGrupal && !empty($clientesIds) && $rutina->plan_id) {
                $idsParaValidar = implode(',', $clientesIds);
                $membresiasValidas = ActiveRecord::queryArray("
                    SELECT DISTINCT usuario_id 
                    FROM membresias 
                    WHERE plan_id = {$rutina->plan_id} 
                    AND estado = 'activa' 
                    AND usuario_id IN ({$idsParaValidar})
                ");
                $idsValidos = array_map(function($m) { return (int)$m['usuario_id']; }, $membresiasValidas);
                
                $invalidos = array_diff($clientesIds, $idsValidos);
                if(!empty($invalidos)) {
                    $nombresInvalidos = Usuario::SQL("SELECT nombre, apellido FROM usuarios WHERE id IN (" . implode(',', $invalidos) . ")");
                    $nombresStr = implode(', ', array_map(function($u) { return $u->nombre . ' ' . $u->apellido; }, $nombresInvalidos));
                    $alertas['error'][] = "Los siguientes alumnos no tienen membresía activa para este plan: {$nombresStr}";
                }
            }

            $rutina->fecha = !empty($_POST['rutina']['fecha']) ? $_POST['rutina']['fecha'] : date('Y-m-d');

            // Determinar si el plan seleccionado es Crossfit
            $plan = Plan::find($rutina->plan_id);
            if($plan && stripos($plan->nombre, 'crossfit') !== false) {
                $rutina->tipo_formato = 'crossfit';
                $rutina->wod_formato = !empty($_POST['rutina']['wod_formato']) ? $_POST['rutina']['wod_formato'] : null;
                $rutina->wod_tiempo = !empty($_POST['rutina']['wod_tiempo']) ? $_POST['rutina']['wod_tiempo'] : null;
                $rutina->wod_descripcion = !empty($_POST['rutina']['wod_descripcion']) ? $_POST['rutina']['wod_descripcion'] : null;
            } else {
                $rutina->tipo_formato = 'estandar';
                $rutina->wod_formato = null;
                $rutina->wod_tiempo = null;
                $rutina->wod_descripcion = null;
            }

            $alertas = array_merge_recursive($alertas, $rutina->validarRutina());

            $ejerciciosPost = $_POST['ejercicios'] ?? [];
            if(empty($ejerciciosPost)) {
                $alertas['error'][] = 'Debés agregar al menos un ejercicio a la rutina';
            }

            if(empty($alertas['error'] ?? [])) {
                if($esGrupal || empty($clientesIds)) {
                    $rutina->cliente_id = null;
                    $resultado = $rutina->guardar();

                    if($resultado) {
                        $rutinaId = (int)($resultado['id'] ?? $rutina->id);
                        self::guardarEjerciciosRutina($rutinaId, $ejerciciosPost);
                        header('Location: /admin/rutinas?resultado=1');
                        return;
                    }
                } else {
                    // Creación por lote: una rutina independiente por cada alumno seleccionado
                    $creadas = 0;
                    foreach($clientesIds as $cId) {
                        $rutinaAlumno = new Rutina();
                        $rutinaAlumno->sincronizar($_POST['rutina'] ?? []);
                        $rutinaAlumno->cliente_id = $cId;
                        $rutinaAlumno->fecha = $rutina->fecha;
                        $rutinaAlumno->tipo_formato = $rutina->tipo_formato;
                        $rutinaAlumno->wod_formato = $rutina->wod_formato;
                        $rutinaAlumno->wod_tiempo = $rutina->wod_tiempo;
                        $rutinaAlumno->wod_descripcion = $rutina->wod_descripcion;

                        $resultado = $rutinaAlumno->guardar();
                        if($resultado) {
                            $rutinaId = (int)($resultado['id'] ?? $rutinaAlumno->id);
                            self::guardarEjerciciosRutina($rutinaId, $ejerciciosPost);
                            $creadas++;
                        }
                    }

                    if($creadas > 0) {
                        header('Location: /admin/rutinas?resultado=1');
                        return;
                    }
                }
            }
        }

        $planes = Plan::whereAll('activo', 1);
        $clientes = Usuario::SQL("SELECT id, nombre, apellido, email FROM usuarios WHERE rol = 'cliente' ORDER BY nombre ASC, apellido ASC");
        $entrenadores = Usuario::SQL("SELECT id, nombre, apellido FROM usuarios WHERE rol = 'entrenador' ORDER BY nombre ASC, apellido ASC");
        $ejercicios = Ejercicio::SQL("SELECT id, nombre, grupo_muscular FROM ejercicios ORDER BY grupo_muscular ASC, nombre ASC");

        $router->render('admin/rutinas/crear', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'rol' => $_SESSION['rol'] ?? '',
            'rutina' => $rutina,
            'planes' => $planes,
            'clientes' => $clientes,
            'entrenadores' => $entrenadores,
            'ejercicios' => $ejercicios,
            'formatosWod' => Rutina::FORMATOS_WOD,
            'alertas' => $alertas,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Edición de una rutina existente
     */
    public static function actualizar(Router $router): void {
        isAdminOEntrenador();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$id) {
            header('Location: /admin/rutinas');
            return;
        }

        $rutina = Rutina::find($id);
        if(!$rutina) {
            header('Location: /admin/rutinas');
            return;
        }

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rutina->sincronizar($_POST['rutina'] ?? []);

            $esGrupal = !empty($_POST['check_rutina_grupal']);
            $clientesIds = $_POST['clientes_ids'] ?? [];

            if(!$esGrupal && empty($clientesIds) && !empty($_POST['rutina']['cliente_id'])) {
                $clientesIds = [(int)$_POST['rutina']['cliente_id']];
            }

            $clientesIds = array_values(array_unique(array_filter(array_map('intval', (array)$clientesIds))));

            if($esGrupal) {
                $rutina->cliente_id = null;
            } else {
                if(!empty($clientesIds)) {
                    $rutina->cliente_id = $clientesIds[0];
                } else {
                    $rutina->cliente_id = null;
                    $alertas['error'][] = 'Debés seleccionar un alumno para la rutina personalizada o marcar la opción de Rutina Grupal';
                }
            }

            // Validar que los alumnos seleccionados tengan membresía activa para este plan
            if(!$esGrupal && !empty($clientesIds) && $rutina->plan_id) {
                $idsParaValidar = implode(',', $clientesIds);
                $membresiasValidas = ActiveRecord::queryArray("
                    SELECT DISTINCT usuario_id 
                    FROM membresias 
                    WHERE plan_id = {$rutina->plan_id} 
                    AND estado = 'activa' 
                    AND usuario_id IN ({$idsParaValidar})
                ");
                $idsValidos = array_map(function($m) { return (int)$m['usuario_id']; }, $membresiasValidas);
                
                $invalidos = array_diff($clientesIds, $idsValidos);
                if(!empty($invalidos)) {
                    $nombresInvalidos = Usuario::SQL("SELECT nombre, apellido FROM usuarios WHERE id IN (" . implode(',', $invalidos) . ")");
                    $nombresStr = implode(', ', array_map(function($u) { return $u->nombre . ' ' . $u->apellido; }, $nombresInvalidos));
                    $alertas['error'][] = "Los siguientes alumnos no tienen membresía activa para este plan: {$nombresStr}";
                }
            }

            $rutina->fecha = !empty($_POST['rutina']['fecha']) ? $_POST['rutina']['fecha'] : date('Y-m-d');

            $plan = Plan::find($rutina->plan_id);
            if($plan && stripos($plan->nombre, 'crossfit') !== false) {
                $rutina->tipo_formato = 'crossfit';
                $rutina->wod_formato = !empty($_POST['rutina']['wod_formato']) ? $_POST['rutina']['wod_formato'] : null;
                $rutina->wod_tiempo = !empty($_POST['rutina']['wod_tiempo']) ? $_POST['rutina']['wod_tiempo'] : null;
                $rutina->wod_descripcion = !empty($_POST['rutina']['wod_descripcion']) ? $_POST['rutina']['wod_descripcion'] : null;
            } else {
                $rutina->tipo_formato = 'estandar';
                $rutina->wod_formato = null;
                $rutina->wod_tiempo = null;
                $rutina->wod_descripcion = null;
            }

            $alertas = array_merge_recursive($alertas, $rutina->validarRutina());

            $ejerciciosPost = $_POST['ejercicios'] ?? [];
            if(empty($ejerciciosPost)) {
                $alertas['error'][] = 'Debés agregar al menos un ejercicio a la rutina';
            }

            if(empty($alertas['error'] ?? [])) {
                $resultado = $rutina->guardar();

                if($resultado) {
                    // Reemplazar ejercicios de la rutina
                    ActiveRecord::SQL("DELETE FROM rutina_ejercicios WHERE rutina_id = {$id}");

                    self::guardarEjerciciosRutina($id, $ejerciciosPost);

                    header('Location: /admin/rutinas?resultado=2');
                    return;
                }
            }
        }

        $planes = Plan::whereAll('activo', 1);
        $clientes = Usuario::SQL("SELECT id, nombre, apellido, email FROM usuarios WHERE rol = 'cliente' ORDER BY nombre ASC, apellido ASC");
        $entrenadores = Usuario::SQL("SELECT id, nombre, apellido FROM usuarios WHERE rol = 'entrenador' ORDER BY nombre ASC, apellido ASC");
        $ejercicios = Ejercicio::SQL("SELECT id, nombre, grupo_muscular FROM ejercicios ORDER BY grupo_muscular ASC, nombre ASC");

        $ejerciciosAsignados = RutinaEjercicio::SQL("
            SELECT rutina_ejercicios.*, ejercicios.nombre AS ejercicio_nombre, ejercicios.grupo_muscular
            FROM rutina_ejercicios
            INNER JOIN ejercicios ON ejercicios.id = rutina_ejercicios.ejercicio_id
            WHERE rutina_ejercicios.rutina_id = {$id}
            ORDER BY rutina_ejercicios.orden ASC, rutina_ejercicios.id ASC
        ");

        $router->render('admin/rutinas/actualizar', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'rol' => $_SESSION['rol'] ?? '',
            'rutina' => $rutina,
            'planes' => $planes,
            'clientes' => $clientes,
            'entrenadores' => $entrenadores,
            'ejercicios' => $ejercicios,
            'ejerciciosAsignados' => $ejerciciosAsignados,
            'formatosWod' => Rutina::FORMATOS_WOD,
            'alertas' => $alertas,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Vista de detalle estilo pizarra de box / workout diario
     */
    public static function detalle(Router $router): void {
        isAdminOEntrenador();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$id) {
            header('Location: /admin/rutinas');
            return;
        }

        $rutinas = Rutina::SQL("
            SELECT rutinas.*, 
                   planes.nombre AS plan_nombre,
                   CONCAT(e.nombre, ' ', e.apellido) AS entrenador_nombre,
                   CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
                   c.email AS cliente_email
            FROM rutinas
            INNER JOIN planes ON planes.id = rutinas.plan_id
            INNER JOIN usuarios e ON e.id = rutinas.entrenador_id
            LEFT JOIN usuarios c ON c.id = rutinas.cliente_id
            WHERE rutinas.id = {$id}
        ");

        $rutina = $rutinas[0] ?? null;
        if(!$rutina) {
            header('Location: /admin/rutinas');
            return;
        }

        // Obtener todos los ejercicios asignados con su técnica y multimedia
        $ejercicios = RutinaEjercicio::SQL("
            SELECT rutina_ejercicios.*, 
                   ejercicios.nombre AS ejercicio_nombre, 
                   ejercicios.grupo_muscular,
                   ejercicios.imagen AS ejercicio_imagen, 
                   ejercicios.video_url, 
                   ejercicios.descripcion
            FROM rutina_ejercicios
            INNER JOIN ejercicios ON ejercicios.id = rutina_ejercicios.ejercicio_id
            WHERE rutina_ejercicios.rutina_id = {$id}
            ORDER BY rutina_ejercicios.orden ASC, rutina_ejercicios.id ASC
        ");

        // Agrupar ejercicios por bloque
        $bloques = [];
        foreach($ejercicios as $ej) {
            $b = $ej->bloque ?: 'general';
            if(!isset($bloques[$b])) {
                $bloques[$b] = [
                    'nombre' => $b,
                    'rondas' => $ej->rondas,
                    'ejercicios' => []
                ];
            }
            $bloques[$b]['ejercicios'][] = $ej;
        }

        $esCrossfit = stripos($rutina->plan_nombre, 'crossfit') !== false;
        $esMusculacion = stripos($rutina->plan_nombre, 'musculaci') !== false;
        $diasEstandar = [];

        if($esCrossfit) {
            $ordenBloques = ['core', 'warmup', 'fuerza', 'wod'];
            $bloquesOrdenados = [];
            foreach($ordenBloques as $ob) {
                if(isset($bloques[$ob])) {
                    $bloquesOrdenados[$ob] = $bloques[$ob];
                } elseif($ob === 'wod' && !empty($rutina->wod_formato)) {
                    $bloquesOrdenados[$ob] = [
                        'nombre' => $ob,
                        'rondas' => null,
                        'ejercicios' => []
                    ];
                }
            }
            foreach($bloques as $k => $v) {
                if(!isset($bloquesOrdenados[$k])) {
                    $bloquesOrdenados[$k] = $v;
                }
            }
            $bloques = $bloquesOrdenados;
        } elseif($esMusculacion && empty($rutina->cliente_id)) {
            // Musculación grupal: división por género dentro de cada día
            foreach($ejercicios as $ej) {
                $diaNum = !empty($ej->dia) ? (int)$ej->dia : 1;
                $b = strtolower($ej->bloque ?: '');
                $genero = (str_contains($b, 'mujeres') || str_contains($b, 'mujer')) ? 'mujeres' : 'hombres';

                if(!isset($diasEstandar[$diaNum])) {
                    $diasEstandar[$diaNum] = [
                        'dia' => $diaNum,
                        'hombres' => [],
                        'mujeres' => []
                    ];
                }
                $diasEstandar[$diaNum][$genero][] = $ej;
            }
            ksort($diasEstandar);
        } else {
            // Funcional / Estándar: rutina unificada sin división de género
            foreach($ejercicios as $ej) {
                $diaNum = !empty($ej->dia) ? (int)$ej->dia : 1;
                if(!isset($diasEstandar[$diaNum])) {
                    $diasEstandar[$diaNum] = [
                        'dia' => $diaNum,
                        'rondas' => $ej->rondas,
                        'ejercicios' => []
                    ];
                } elseif(empty($diasEstandar[$diaNum]['rondas']) && !empty($ej->rondas)) {
                    $diasEstandar[$diaNum]['rondas'] = $ej->rondas;
                }
                $diasEstandar[$diaNum]['ejercicios'][] = $ej;
            }
            ksort($diasEstandar);
        }

        $router->render('admin/rutinas/detalle', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'rol' => $_SESSION['rol'] ?? '',
            'rutina' => $rutina,
            'bloques' => $bloques,
            'diasEstandar' => $diasEstandar,
            'esCrossfit' => $esCrossfit,
            'esMusculacion' => $esMusculacion,
            'totalEjercicios' => count($ejercicios),
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Eliminar rutina
     */
    public static function eliminar(): void {
        isAdminOEntrenador();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if($id) {
                $rutina = Rutina::find($id);
                if($rutina) {
                    $rutina->eliminar();
                    header('Location: /admin/rutinas?resultado=3');
                    return;
                }
            }
        }

        header('Location: /admin/rutinas');
    }

    /**
     * Guarda los ejercicios asignados a una rutina
     */
    private static function guardarEjerciciosRutina(int $rutinaId, array $ejerciciosPost): void {
        $orden = 1;
        foreach($ejerciciosPost as $item) {
            $ejercicioId = filter_var($item['ejercicio_id'] ?? null, FILTER_VALIDATE_INT);
            if(!$ejercicioId) continue;

            $rutinaEj = new RutinaEjercicio([
                'rutina_id' => $rutinaId,
                'ejercicio_id' => $ejercicioId,
                'bloque' => filter_var($item['bloque'] ?? 'general', FILTER_SANITIZE_SPECIAL_CHARS),
                'rondas' => !empty($item['rondas']) ? (int)$item['rondas'] : null,
                'series' => !empty($item['series']) ? (int)$item['series'] : null,
                'reps' => !empty($item['reps']) ? (int)$item['reps'] : null,
                'peso_hombres' => filter_var($item['peso_hombres'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS),
                'peso_mujeres' => filter_var($item['peso_mujeres'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS),
                'dia' => !empty($item['dia']) ? (int)$item['dia'] : 1,
                'orden' => $orden++,
                'notas' => filter_var($item['notas'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS)
            ]);

            $rutinaEj->guardar();
        }
    }
}
