<?php

namespace Controllers;

use MVC\Router;
use Model\Reserva;
use Model\Horario;
use Model\Plan;
use Model\Usuario;
use Model\ActiveRecord;
use Model\Pago;

class AdminController {

    public static function index(Router $router): void {
        isAdmin();

        // 1. Métricas clave en tiempo real del gimnasio
        $totalClientes = 0;
        $resClientes = ActiveRecord::queryArray("SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'cliente'");
        if(!empty($resClientes[0]['total'])) {
            $totalClientes = (int)$resClientes[0]['total'];
        }

        $totalProfesores = 0;
        $resProfesores = ActiveRecord::queryArray("SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'entrenador'");
        if(!empty($resProfesores[0]['total'])) {
            $totalProfesores = (int)$resProfesores[0]['total'];
        }

        $totalPlanes = 0;
        $resPlanes = ActiveRecord::queryArray("SELECT COUNT(*) AS total FROM planes WHERE activo = 1");
        if(!empty($resPlanes[0]['total'])) {
            $totalPlanes = (int)$resPlanes[0]['total'];
        }

        // Reservas y asistencia de hoy
        $reservasHoy = 0;
        $presentesHoy = 0;
        $resHoy = ActiveRecord::queryArray("
            SELECT 
                COUNT(reservas.id) AS total,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS presentes
            FROM reservas
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE reservas.fecha = CURDATE() AND reservas.estado = 'reservada'
        ");
        if(!empty($resHoy[0])) {
            $reservasHoy = (int)($resHoy[0]['total'] ?? 0);
            $presentesHoy = (int)($resHoy[0]['presentes'] ?? 0);
        }

        // Tasa de asistencia del mes actual
        $tasaAsistenciaMes = 0;
        $resMes = ActiveRecord::queryArray("
            SELECT 
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN asistencias.presente = 0 THEN 1 ELSE 0 END) AS ausentes
            FROM reservas
            INNER JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE MONTH(reservas.fecha) = MONTH(CURDATE()) AND YEAR(reservas.fecha) = YEAR(CURDATE())
            AND reservas.estado != 'cancelada'
        ");
        if(!empty($resMes[0])) {
            $pMes = (int)($resMes[0]['presentes'] ?? 0);
            $aMes = (int)($resMes[0]['ausentes'] ?? 0);
            $evalMes = $pMes + $aMes;
            $tasaAsistenciaMes = ($evalMes > 0) ? round(($pMes / $evalMes) * 100) : 100;
        }

        $kpis = [
            'totalClientes' => $totalClientes,
            'totalProfesores' => $totalProfesores,
            'totalPlanes' => $totalPlanes,
            'reservasHoy' => $reservasHoy,
            'presentesHoy' => $presentesHoy,
            'tasaAsistenciaMes' => $tasaAsistenciaMes
        ];

        $router->render('admin/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'kpis' => $kpis,
            'tipo' => 'dashboard'
        ]);
    }

    public static function reservas(Router $router): void {
        isAdmin();

        $fecha = filter_var($_GET['fecha'] ?? date('Y-m-d'), FILTER_SANITIZE_SPECIAL_CHARS);
        if(!$fecha || !strtotime($fecha)) {
            $fecha = date('Y-m-d');
        }

        $timestamp = strtotime($fecha);
        $diaSemana = (int)date('w', $timestamp);
        $fechaAnterior = date('Y-m-d', strtotime($fecha . ' -1 day'));
        $fechaSiguiente = date('Y-m-d', strtotime($fecha . ' +1 day'));
        $esHoy = ($fecha === date('Y-m-d'));

        // Obtener todas las clases programadas para este día de la semana
        $horarios = Horario::SQL("
            SELECT horarios.*, planes.nombre AS plan_nombre, planes.imagen AS plan_imagen,
                   CONCAT(profesores.nombre, ' ', profesores.apellido) AS entrenador
            FROM horarios
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios AS profesores ON profesores.id = horarios.entrenador_id
            WHERE horarios.dia_semana = {$diaSemana} AND planes.activo = 1
            ORDER BY horarios.hora_inicio ASC, planes.nombre ASC
        ");

        // Obtener todas las reservas activas para esta fecha con su estado de asistencia
        $reservas = Reserva::SQL("
            SELECT reservas.*, horarios.hora_inicio, horarios.hora_fin, planes.nombre AS plan_nombre,
                   CONCAT(profesores.nombre, ' ', profesores.apellido) AS entrenador,
                   CONCAT(clientes.nombre, ' ', clientes.apellido) AS cliente_nombre,
                   clientes.email AS cliente_email,
                   clientes.telefono AS cliente_telefono,
                   asistencias.id AS asistencia_id,
                   asistencias.presente AS asistencia_presente
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios AS profesores ON profesores.id = horarios.entrenador_id
            INNER JOIN usuarios AS clientes ON clientes.id = reservas.usuario_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE reservas.fecha = '{$fecha}' AND reservas.estado = 'reservada'
            ORDER BY horarios.hora_inicio ASC, clientes.nombre ASC
        ");

        // Agrupar reservas por ID de horario
        $reservasPorHorario = [];
        $totalPresentes = 0;
        $totalAusentes = 0;

        foreach($reservas as $res) {
            $reservasPorHorario[$res->horario_id][] = $res;
            if(!is_null($res->asistencia_presente)) {
                if((int)$res->asistencia_presente === 1) {
                    $totalPresentes++;
                } else {
                    $totalAusentes++;
                }
            }
        }

        // Estadísticas de la jornada
        $totalClases = count($horarios);
        $totalAlumnos = count($reservas);
        $capacidadTotal = 0;
        $tieneIlimitados = false;
        foreach($horarios as $h) {
            if(!is_null($h->cupo) && $h->cupo !== '') {
                $capacidadTotal += (int)$h->cupo;
            } else {
                $tieneIlimitados = true;
            }
        }
        $porcentajeOcupacion = ($capacidadTotal > 0) ? round(($totalAlumnos / $capacidadTotal) * 100) : 0;

        $totalEvaluadas = $totalPresentes + $totalAusentes;
        $tasaAsistenciaDia = ($totalEvaluadas > 0) ? round(($totalPresentes / $totalEvaluadas) * 100) : 0;

        $router->render('admin/reservas/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'fecha' => $fecha,
            'diaSemana' => $diaSemana,
            'fechaAnterior' => $fechaAnterior,
            'fechaSiguiente' => $fechaSiguiente,
            'esHoy' => $esHoy,
            'horarios' => $horarios,
            'reservasPorHorario' => $reservasPorHorario,
            'totalClases' => $totalClases,
            'totalAlumnos' => $totalAlumnos,
            'capacidadTotal' => $capacidadTotal,
            'tieneIlimitados' => $tieneIlimitados,
            'porcentajeOcupacion' => $porcentajeOcupacion,
            'totalPresentes' => $totalPresentes,
            'totalAusentes' => $totalAusentes,
            'tasaAsistenciaDia' => $tasaAsistenciaDia,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Reportes y Métricas de Negocio y Asistencia (Etapa 6)
     */
    public static function reportes(Router $router): void {
        isAdmin();

        $periodo = filter_var($_GET['periodo'] ?? 'mes_actual', FILTER_SANITIZE_SPECIAL_CHARS);

        $mesesEspanol = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $mesActualNum = (int)date('n');
        $anioActual = (int)date('Y');

        switch($periodo) {
            case 'mes_anterior':
                $whereFecha = "YEAR(reservas.fecha) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) AND MONTH(reservas.fecha) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
                $mesAntNum = (int)date('n', strtotime('-1 month'));
                $anioAnt = (int)date('Y', strtotime('-1 month'));
                $tituloPeriodo = "Mes Anterior ({$mesesEspanol[$mesAntNum]} {$anioAnt})";
                break;
            case 'ultimos_30':
                $whereFecha = "reservas.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND reservas.fecha <= CURDATE()";
                $tituloPeriodo = "Últimos 30 Días";
                break;
            case 'anio_actual':
                $whereFecha = "YEAR(reservas.fecha) = {$anioActual}";
                $tituloPeriodo = "Año {$anioActual}";
                break;
            case 'historico':
                $whereFecha = "1=1";
                $tituloPeriodo = "Histórico Completo";
                break;
            case 'mes_actual':
            default:
                $periodo = 'mes_actual';
                $whereFecha = "YEAR(reservas.fecha) = {$anioActual} AND MONTH(reservas.fecha) = {$mesActualNum}";
                $tituloPeriodo = "Mes Actual ({$mesesEspanol[$mesActualNum]} {$anioActual})";
                break;
        }

        // 1. KPIs Generales de Asistencia y Reservas
        $kpiRows = ActiveRecord::queryArray("
            SELECT 
                COUNT(reservas.id) AS total_reservas,
                SUM(CASE WHEN reservas.estado = 'reservada' THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN reservas.estado = 'cancelada' THEN 1 ELSE 0 END) AS canceladas,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN asistencias.presente = 0 THEN 1 ELSE 0 END) AS ausentes
            FROM reservas
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE {$whereFecha}
        ");

        $kpiData = $kpiRows[0] ?? [];
        $totalReservas = (int)($kpiData['total_reservas'] ?? 0);
        $totalActivas = (int)($kpiData['activas'] ?? 0);
        $totalCanceladas = (int)($kpiData['canceladas'] ?? 0);
        $totalPresentes = (int)($kpiData['presentes'] ?? 0);
        $totalAusentes = (int)($kpiData['ausentes'] ?? 0);
        $totalEvaluadas = $totalPresentes + $totalAusentes;

        $tasaAsistencia = ($totalEvaluadas > 0) ? round(($totalPresentes / $totalEvaluadas) * 100) : 0;
        $tasaAusentismo = ($totalEvaluadas > 0) ? round(($totalAusentes / $totalEvaluadas) * 100) : 0;
        $tasaCancelacion = ($totalReservas > 0) ? round(($totalCanceladas / $totalReservas) * 100) : 0;

        // 2. Asistencia por Disciplina / Plan
        $porDisciplina = ActiveRecord::queryArray("
            SELECT 
                planes.id,
                planes.nombre,
                planes.precio,
                COUNT(reservas.id) AS total_reservas,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN asistencias.presente = 0 THEN 1 ELSE 0 END) AS ausentes,
                SUM(CASE WHEN reservas.estado = 'cancelada' THEN 1 ELSE 0 END) AS canceladas
            FROM planes
            INNER JOIN horarios ON horarios.plan_id = planes.id
            INNER JOIN reservas ON reservas.horario_id = horarios.id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE {$whereFecha}
            GROUP BY planes.id, planes.nombre, planes.precio
            ORDER BY presentes DESC, total_reservas DESC
        ");

        // 3. Ranking de Alumnos Más Constantes (Top 10)
        $rankingAlumnos = ActiveRecord::queryArray("
            SELECT 
                usuarios.id,
                usuarios.nombre,
                usuarios.apellido,
                usuarios.email,
                usuarios.telefono,
                COUNT(reservas.id) AS total_reservas,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN asistencias.presente = 0 THEN 1 ELSE 0 END) AS ausentes
            FROM usuarios
            INNER JOIN reservas ON reservas.usuario_id = usuarios.id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE {$whereFecha} AND usuarios.rol = 'cliente'
            GROUP BY usuarios.id, usuarios.nombre, usuarios.apellido, usuarios.email, usuarios.telefono
            ORDER BY presentes DESC, total_reservas DESC
            LIMIT 10
        ");

        // 4. Concurrencia por Día de la Semana
        $porDiaSemanaRaw = ActiveRecord::queryArray("
            SELECT 
                horarios.dia_semana,
                COUNT(reservas.id) AS total_reservas,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS presentes
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE {$whereFecha}
            GROUP BY horarios.dia_semana
            ORDER BY horarios.dia_semana ASC
        ");

        $diasMap = [
            1 => ['nombre' => 'Lunes', 'reservas' => 0, 'presentes' => 0],
            2 => ['nombre' => 'Martes', 'reservas' => 0, 'presentes' => 0],
            3 => ['nombre' => 'Miércoles', 'reservas' => 0, 'presentes' => 0],
            4 => ['nombre' => 'Jueves', 'reservas' => 0, 'presentes' => 0],
            5 => ['nombre' => 'Viernes', 'reservas' => 0, 'presentes' => 0],
            6 => ['nombre' => 'Sábado', 'reservas' => 0, 'presentes' => 0]
        ];

        foreach($porDiaSemanaRaw as $dia) {
            $d = (int)$dia['dia_semana'];
            if(isset($diasMap[$d])) {
                $diasMap[$d]['reservas'] = (int)$dia['total_reservas'];
                $diasMap[$d]['presentes'] = (int)$dia['presentes'];
            }
        }

        // 5. Top Horarios / Turnos con mayor asistencia
        $topHorarios = ActiveRecord::queryArray("
            SELECT 
                horarios.dia_semana,
                horarios.hora_inicio,
                horarios.hora_fin,
                planes.nombre AS plan_nombre,
                CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS entrenador,
                COUNT(reservas.id) AS total_reservas,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS presentes
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios ON usuarios.id = horarios.entrenador_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE {$whereFecha}
            GROUP BY horarios.id, horarios.dia_semana, horarios.hora_inicio, horarios.hora_fin, planes.nombre, usuarios.nombre, usuarios.apellido
            ORDER BY presentes DESC, total_reservas DESC
            LIMIT 5
        ");

        $router->render('admin/reportes', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'periodo' => $periodo,
            'tituloPeriodo' => $tituloPeriodo,
            'kpis' => [
                'totalReservas' => $totalReservas,
                'totalActivas' => $totalActivas,
                'totalCanceladas' => $totalCanceladas,
                'totalPresentes' => $totalPresentes,
                'totalAusentes' => $totalAusentes,
                'totalEvaluadas' => $totalEvaluadas,
                'tasaAsistencia' => $tasaAsistencia,
                'tasaAusentismo' => $tasaAusentismo,
                'tasaCancelacion' => $tasaCancelacion
            ],
            'porDisciplina' => $porDisciplina,
            'rankingAlumnos' => $rankingAlumnos,
            'diasMap' => $diasMap,
            'topHorarios' => $topHorarios,
            'tipo' => 'dashboard'
        ]);
    }

    public static function pagos(Router $router): void {
        isAdmin();

        $periodo = filter_var($_GET['periodo'] ?? 'mes_actual', FILTER_SANITIZE_SPECIAL_CHARS);
        $filtro = Pago::obtenerFiltroPeriodo($periodo);

        $kpis = Pago::obtenerKpisPorPeriodo($filtro['where']);
        $pagos = Pago::obtenerPagosPorPeriodo($filtro['where']);
        $porPlan = Pago::obtenerRecaudacionPorPlan($filtro['where']);
        $proximosVencimientos = Pago::obtenerProximosVencimientos(7);

        $router->render('admin/pagos/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'periodo' => $filtro['periodo'],
            'tituloPeriodo' => $filtro['titulo'],
            'kpis' => $kpis,
            'pagos' => $pagos,
            'porPlan' => $porPlan,
            'proximosVencimientos' => $proximosVencimientos,
            'tipo' => 'dashboard'
        ]);
    }
}

