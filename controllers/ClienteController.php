<?php

namespace Controllers;

use MVC\Router;
use Model\Reserva;
use Model\Horario;
use Model\Plan;
use Model\Rutina;
use Model\RutinaEjercicio;
use Model\ActiveRecord;

class ClienteController {

    public static function index(Router $router): void {
        isCliente();

        $usuarioId = (int)$_SESSION['id'];

        // Obtener planes activos del cliente con cálculo de días restantes
        $membresiasActivas = ActiveRecord::queryArray("
            SELECT 
                membresias.id,
                membresias.plan_id,
                membresias.fecha_inicio,
                membresias.fecha_fin,
                planes.nombre,
                DATEDIFF(membresias.fecha_fin, CURDATE()) AS dias_restantes
            FROM membresias
            INNER JOIN planes ON planes.id = membresias.plan_id
            WHERE membresias.usuario_id = {$usuarioId} 
              AND membresias.estado = 'activa'
            ORDER BY membresias.fecha_fin ASC
        ");

        // Obtener próximos turnos activos a partir de hoy
        $proximosTurnos = Reserva::SQL("
            SELECT reservas.*, horarios.hora_inicio, horarios.hora_fin, horarios.descripcion, planes.nombre AS plan_nombre,
                   CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS entrenador
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios ON usuarios.id = horarios.entrenador_id
            WHERE reservas.usuario_id = {$usuarioId}
            AND reservas.estado = 'reservada'
            AND (reservas.fecha > CURDATE() OR (reservas.fecha = CURDATE() AND horarios.hora_fin > CURTIME()))
            ORDER BY reservas.fecha ASC, horarios.hora_inicio ASC
            LIMIT 5
        ");

        $router->render('cliente/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'proximosTurnos' => $proximosTurnos,
            'membresiasActivas' => $membresiasActivas,
            'tipo' => 'dashboard'
        ]);
    }

    public static function reservar(Router $router): void {
        isCliente();

        $usuarioId = (int)$_SESSION['id'];

        $planes = Plan::SQL("
            SELECT planes.* 
            FROM planes 
            INNER JOIN membresias ON membresias.plan_id = planes.id
            WHERE planes.activo = 1 
              AND membresias.usuario_id = {$usuarioId} 
              AND membresias.estado = 'activa'
            ORDER BY planes.nombre ASC
        ");

        $router->render('cliente/reservar', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'planes' => $planes,
            'tipo' => 'dashboard'
        ]);
    }

    public static function turnos(Router $router): void {
        isCliente();

        $usuarioId = (int)$_SESSION['id'];

        // Próximos turnos reservados ordenados de menor a mayor por fecha y horario (con estado de asistencia si ya se tomó hoy)
        $turnosFuturos = Reserva::SQL("
            SELECT reservas.*, horarios.hora_inicio, horarios.hora_fin, horarios.descripcion, planes.nombre AS plan_nombre,
                   CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS entrenador,
                   asistencias.id AS asistencia_id,
                   asistencias.presente AS asistencia_presente
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios ON usuarios.id = horarios.entrenador_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE reservas.usuario_id = {$usuarioId}
            AND reservas.estado = 'reservada'
            AND (reservas.fecha > CURDATE() OR (reservas.fecha = CURDATE() AND horarios.hora_fin > CURTIME()))
            ORDER BY reservas.fecha ASC, horarios.hora_inicio ASC
        ");

        // Historial de turnos pasados o cancelados con su control de asistencia
        $turnosPasados = Reserva::SQL("
            SELECT reservas.*, horarios.hora_inicio, horarios.hora_fin, horarios.descripcion, planes.nombre AS plan_nombre,
                   CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS entrenador,
                   asistencias.id AS asistencia_id,
                   asistencias.presente AS asistencia_presente
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios ON usuarios.id = horarios.entrenador_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE reservas.usuario_id = {$usuarioId}
            AND (
                reservas.fecha < CURDATE() 
                OR (reservas.fecha = CURDATE() AND horarios.hora_fin <= CURTIME())
                OR reservas.estado = 'cancelada'
            )
            ORDER BY reservas.fecha DESC, horarios.hora_inicio DESC
        ");

        // Métricas de asistencia del alumno
        $totalAsistidas = 0;
        $totalAusencias = 0;
        $totalCompletadas = 0;

        foreach($turnosPasados as $tp) {
            if($tp->estado !== 'cancelada') {
                $totalCompletadas++;
                if(!is_null($tp->asistencia_presente)) {
                    if((int)$tp->asistencia_presente === 1) {
                        $totalAsistidas++;
                    } else {
                        $totalAusencias++;
                    }
                }
            }
        }

        $totalEvaluadas = $totalAsistidas + $totalAusencias;
        $porcentajeAsistencia = ($totalEvaluadas > 0) ? round(($totalAsistidas / $totalEvaluadas) * 100) : 100;

        $statsAsistencia = [
            'totalAsistidas' => $totalAsistidas,
            'totalAusencias' => $totalAusencias,
            'totalCompletadas' => $totalCompletadas,
            'porcentajeAsistencia' => $porcentajeAsistencia
        ];

        $router->render('cliente/turnos', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'turnosFuturos' => $turnosFuturos,
            'turnosPasados' => $turnosPasados,
            'statsAsistencia' => $statsAsistencia,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Vista de Mi Rutina / WOD Diario del Alumno
     */
    public static function rutina(Router $router): void {
        isCliente();

        $usuarioId = (int)$_SESSION['id'];

        // 1. Obtener todos los planes activos del usuario
        $planes = Plan::SQL("
            SELECT planes.* 
            FROM planes 
            INNER JOIN membresias ON membresias.plan_id = planes.id
            WHERE planes.activo = 1 
              AND membresias.usuario_id = {$usuarioId} 
              AND membresias.estado = 'activa'
            ORDER BY planes.nombre ASC
        ");

        // 2. Verificar si el alumno tiene alguna rutina personalizada asignada
        $rutinasPersonalizadas = Rutina::SQL("
            SELECT id, nombre, fecha 
            FROM rutinas 
            WHERE cliente_id = {$usuarioId} 
            ORDER BY fecha DESC, id DESC
        ");
        $tienePersonalizada = !empty($rutinasPersonalizadas);

        // 3. Parámetros de filtrado
        $fecha = filter_var($_GET['fecha'] ?? date('Y-m-d'), FILTER_SANITIZE_SPECIAL_CHARS);
        $tipoVista = filter_var($_GET['tipo'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS); // 'personalizada' o vacío
        $planId = filter_var($_GET['plan_id'] ?? null, FILTER_VALIDATE_INT);

        // Si no seleccionó plan, intentar deducirlo de su membresía activa o de su primer plan disponible
        if(!$planId && $tipoVista !== 'personalizada') {
            $membresia = ActiveRecord::SQL("
                SELECT plan_id 
                FROM membresias 
                WHERE usuario_id = {$usuarioId} AND estado = 'activa' 
                LIMIT 1
            ");
            if(!empty($membresia[0]->plan_id)) {
                $planId = (int)$membresia[0]->plan_id;
            } elseif(!empty($planes[0]->id)) {
                $planId = (int)$planes[0]->id;
            }
        }

        $rutina = null;
        $esUltimaDisponible = false;

        if($tipoVista === 'personalizada') {
            // Rutina personalizada para este alumno
            $rutinas = Rutina::SQL("
                SELECT rutinas.*, 
                       planes.nombre AS plan_nombre,
                       CONCAT(e.nombre, ' ', e.apellido) AS entrenador_nombre
                FROM rutinas
                INNER JOIN planes ON planes.id = rutinas.plan_id
                INNER JOIN usuarios e ON e.id = rutinas.entrenador_id
                WHERE rutinas.cliente_id = {$usuarioId}
                ORDER BY rutinas.fecha DESC, rutinas.id DESC
                LIMIT 1
            ");
            $rutina = $rutinas[0] ?? null;
        } elseif(!empty($planId)) {
            // Buscar la rutina del día para el plan seleccionado (grupal o personalizada)
            $rutinas = Rutina::SQL("
                SELECT rutinas.*, 
                       planes.nombre AS plan_nombre,
                       CONCAT(e.nombre, ' ', e.apellido) AS entrenador_nombre
                FROM rutinas
                INNER JOIN planes ON planes.id = rutinas.plan_id
                INNER JOIN usuarios e ON e.id = rutinas.entrenador_id
                WHERE rutinas.plan_id = {$planId} 
                AND rutinas.fecha = '{$fecha}' 
                AND (rutinas.cliente_id IS NULL OR rutinas.cliente_id = {$usuarioId})
                ORDER BY rutinas.cliente_id DESC
                LIMIT 1
            ");
            $rutina = $rutinas[0] ?? null;

            // Si para la fecha exacta no hay rutina cargada, buscar la última rutina registrada de ese plan
            if(!$rutina) {
                $ultimas = Rutina::SQL("
                    SELECT rutinas.*, 
                           planes.nombre AS plan_nombre,
                           CONCAT(e.nombre, ' ', e.apellido) AS entrenador_nombre
                    FROM rutinas
                    INNER JOIN planes ON planes.id = rutinas.plan_id
                    INNER JOIN usuarios e ON e.id = rutinas.entrenador_id
                    WHERE rutinas.plan_id = {$planId} 
                    AND (rutinas.cliente_id IS NULL OR rutinas.cliente_id = {$usuarioId})
                    ORDER BY rutinas.fecha DESC, rutinas.cliente_id DESC, rutinas.id DESC
                    LIMIT 1
                ");
                if(!empty($ultimas[0])) {
                    $rutina = $ultimas[0];
                    $esUltimaDisponible = true;
                }
            }
        }

        // 4. Si encontramos rutina, obtener los ejercicios con imágenes y videos
        $bloques = [];
        $diasEstandar = [];
        $esCrossfit = false;
        $esMusculacion = false;

        if($rutina) {
            $esCrossfit = stripos($rutina->plan_nombre, 'crossfit') !== false;
            $esMusculacion = stripos($rutina->plan_nombre, 'musculaci') !== false;

            $ejercicios = RutinaEjercicio::SQL("
                SELECT rutina_ejercicios.*, 
                       ejercicios.nombre AS ejercicio_nombre, 
                       ejercicios.grupo_muscular,
                       ejercicios.imagen AS ejercicio_imagen, 
                       ejercicios.video_url, 
                       ejercicios.descripcion
                FROM rutina_ejercicios
                INNER JOIN ejercicios ON ejercicios.id = rutina_ejercicios.ejercicio_id
                WHERE rutina_ejercicios.rutina_id = {$rutina->id}
                ORDER BY rutina_ejercicios.orden ASC, rutina_ejercicios.id ASC
            ");

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

            // Ordenar las 4 partes reglamentarias de Crossfit

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
        }

        $router->render('cliente/rutina', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'planes' => $planes,
            'planSeleccionado' => $planId,
            'fechaSeleccionada' => $fecha,
            'tipoVista' => $tipoVista,
            'tienePersonalizada' => $tienePersonalizada,
            'rutina' => $rutina,
            'bloques' => $bloques,
            'diasEstandar' => $diasEstandar,
            'esCrossfit' => $esCrossfit,
            'esMusculacion' => $esMusculacion,
            'esUltimaDisponible' => $esUltimaDisponible,
            'tipo' => 'dashboard'
        ]);
    }

    public static function planes(Router $router): void {
        isCliente();

        $usuarioId = (int)$_SESSION['id'];

        $planes = Plan::SQL("SELECT * FROM planes WHERE activo = 1 ORDER BY precio ASC");

        // Obtener IDs y detalles de planes que el usuario ya tiene activos
        $membresiasRaw = ActiveRecord::queryArray("
            SELECT 
                id, 
                plan_id, 
                fecha_inicio, 
                fecha_fin, 
                DATEDIFF(fecha_fin, CURDATE()) AS dias_restantes 
            FROM membresias 
            WHERE usuario_id = {$usuarioId} AND estado = 'activa'
        ");
        
        $membresiasActivas = [];
        $detallesMembresias = [];
        foreach($membresiasRaw as $mem) {
            $membresiasActivas[] = (int)$mem['plan_id'];
            $detallesMembresias[(int)$mem['plan_id']] = $mem;
        }

        $router->render('cliente/planes', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'planes' => $planes,
            'membresiasActivas' => $membresiasActivas,
            'detallesMembresias' => $detallesMembresias,
            'tipo' => 'dashboard'
        ]);
    }
}
