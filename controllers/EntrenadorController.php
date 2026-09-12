<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\Horario;
use Model\Reserva;
use Model\ActiveRecord;

class EntrenadorController {

    /**
     * Dashboard para el entrenador logueado
     */
    public static function index(Router $router): void {
        isEntrenador();

        $router->render('entrenador/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Vista de control de asistencia para las clases del entrenador
     */
    public static function asistencia(Router $router): void {
        isEntrenador();

        $entrenadorId = (int)$_SESSION['id'];
        $fecha = filter_var($_GET['fecha'] ?? date('Y-m-d'), FILTER_SANITIZE_SPECIAL_CHARS);

        if(!$fecha || !strtotime($fecha)) {
            $fecha = date('Y-m-d');
        }

        $diaSemana = (int)date('w', strtotime($fecha));
        $esHoy = ($fecha === date('Y-m-d'));
        $fechaAnterior = date('Y-m-d', strtotime($fecha . ' -1 day'));
        $fechaSiguiente = date('Y-m-d', strtotime($fecha . ' +1 day'));

        // Obtener solo las clases asignadas a este profesor en este día de la semana
        $horarios = Horario::SQL("
            SELECT horarios.*, planes.nombre AS plan_nombre
            FROM horarios
            INNER JOIN planes ON planes.id = horarios.plan_id
            WHERE horarios.entrenador_id = {$entrenadorId} 
            AND horarios.dia_semana = {$diaSemana}
            AND planes.activo = 1
            ORDER BY horarios.hora_inicio ASC
        ");

        // Obtener las reservas de esas clases para esta fecha con estado de asistencia
        $reservas = Reserva::SQL("
            SELECT reservas.*, horarios.hora_inicio, horarios.hora_fin, planes.nombre AS plan_nombre,
                   CONCAT(clientes.nombre, ' ', clientes.apellido) AS cliente_nombre,
                   clientes.email AS cliente_email,
                   clientes.telefono AS cliente_telefono,
                   asistencias.id AS asistencia_id,
                   asistencias.presente AS asistencia_presente
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios AS clientes ON clientes.id = reservas.usuario_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE horarios.entrenador_id = {$entrenadorId}
            AND reservas.fecha = '{$fecha}'
            AND reservas.estado = 'reservada'
            ORDER BY horarios.hora_inicio ASC, clientes.nombre ASC
        ");

        $reservasPorHorario = [];
        foreach($reservas as $res) {
            $reservasPorHorario[$res->horario_id][] = $res;
        }

        $totalClases = count($horarios);
        $totalAlumnos = count($reservas);
        $totalPresentes = 0;
        foreach($reservas as $r) {
            if((int)$r->asistencia_presente === 1) {
                $totalPresentes++;
            }
        }

        $router->render('entrenador/asistencia', [
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
            'totalPresentes' => $totalPresentes,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Listado de entrenadores para el Administrador
     */
    public static function listar(Router $router): void {
        isAdmin();

        $resultado = $_GET['resultado'] ?? null;

        // Obtener todos los entrenadores con la cantidad de clases y nombres de disciplinas que dictan
        $entrenadores = Usuario::SQL("
            SELECT usuarios.*, 
                   COUNT(DISTINCT horarios.id) AS total_clases,
                   GROUP_CONCAT(DISTINCT planes.nombre ORDER BY planes.nombre SEPARATOR ', ') AS clases_nombres
            FROM usuarios
            LEFT JOIN horarios ON horarios.entrenador_id = usuarios.id
            LEFT JOIN planes ON planes.id = horarios.plan_id
            WHERE usuarios.rol = 'entrenador'
            GROUP BY usuarios.id
            ORDER BY usuarios.nombre ASC, usuarios.apellido ASC
        ");

        $router->render('admin/entrenadores/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'entrenadores' => $entrenadores,
            'resultado' => $resultado,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Formulario de creación de un nuevo entrenador (por el admin)
     */
    public static function crear(Router $router): void {
        isAdmin();

        $alertas = [];
        $entrenador = new Usuario();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $entrenador->sincronizar($_POST['entrenador'] ?? $_POST);
            $entrenador->rol = 'entrenador';
            $entrenador->confirmado = 1;

            $alertas = $entrenador->validarEntrenador(true);

            if(empty($alertas)) {
                // Verificar que no exista otro usuario con ese email
                $yaExiste = $entrenador->existeUsuarioExcluyendoId();
                if($yaExiste) {
                    $alertas = Usuario::getAlertas();
                } else {
                    $entrenador->hashPassword();
                    $resultado = $entrenador->guardar();

                    if($resultado) {
                        header('Location: /admin/entrenadores?resultado=1');
                        return;
                    }
                }
            }
        }

        $router->render('admin/entrenadores/crear', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'entrenador' => $entrenador,
            'alertas' => $alertas,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Formulario de edición de un entrenador existente
     */
    public static function actualizar(Router $router): void {
        isAdmin();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$id) {
            header('Location: /admin/entrenadores');
            return;
        }

        $entrenador = Usuario::find($id);
        if(!$entrenador || $entrenador->rol !== 'entrenador') {
            header('Location: /admin/entrenadores');
            return;
        }

        $alertas = [];
        $passwordOriginal = $entrenador->password;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $_POST['entrenador'] ?? $_POST;
            $passwordNuevo = trim($datos['password'] ?? '');

            $entrenador->sincronizar($datos);
            $entrenador->rol = 'entrenador';
            $entrenador->confirmado = 1;

            $alertas = $entrenador->validarEntrenador(false);

            if(empty($alertas)) {
                $yaExiste = $entrenador->existeUsuarioExcluyendoId((int)$entrenador->id);
                if($yaExiste) {
                    $alertas = Usuario::getAlertas();
                } else {
                    // Si completó una nueva contraseña la hasheamos, sino preservamos la actual
                    if(!empty($passwordNuevo)) {
                        $entrenador->password = $passwordNuevo;
                        $entrenador->hashPassword();
                    } else {
                        $entrenador->password = $passwordOriginal;
                    }

                    $resultado = $entrenador->guardar();

                    if($resultado) {
                        header('Location: /admin/entrenadores?resultado=2');
                        return;
                    }
                }
            }
        }

        // Limpiar el hash de la contraseña para no mostrarlo en el input del formulario
        $entrenador->password = '';

        $router->render('admin/entrenadores/actualizar', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'entrenador' => $entrenador,
            'alertas' => $alertas,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Eliminación de un entrenador
     */
    public static function eliminar(): void {
        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if($id) {
                $entrenador = Usuario::find($id);

                if($entrenador && $entrenador->rol === 'entrenador') {
                    // Verificar si tiene clases asignadas en horarios
                    $clasesAsignadas = Horario::SQL("
                        SELECT id FROM horarios WHERE entrenador_id = {$id} LIMIT 1
                    ");

                    if(!empty($clasesAsignadas)) {
                        header('Location: /admin/entrenadores?error=clases_asignadas');
                        return;
                    }

                    $entrenador->eliminar();
                    header('Location: /admin/entrenadores?resultado=3');
                    return;
                }
            }
        }

        header('Location: /admin/entrenadores');
    }

    /**
     * Listado y seguimiento de alumnos para el entrenador
     */
    public static function alumnos(Router $router): void {
        isEntrenador();

        $entrenadorId = (int)$_SESSION['id'];

        // KPIs específicos del entrenador
        $kpis = [
            'totalAlumnosClases' => 0,
            'totalAsistenciasDadas' => 0,
            'totalRutinasAsignadas' => 0
        ];

        $resKpis = ActiveRecord::queryArray("
            SELECT 
                COUNT(DISTINCT reservas.usuario_id) AS alumnos_clases,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS total_asistencias
            FROM horarios
            INNER JOIN reservas ON reservas.horario_id = horarios.id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE horarios.entrenador_id = {$entrenadorId}
        ");

        if(!empty($resKpis[0])) {
            $kpis['totalAlumnosClases'] = (int)($resKpis[0]['alumnos_clases'] ?? 0);
            $kpis['totalAsistenciasDadas'] = (int)($resKpis[0]['total_asistencias'] ?? 0);
        }

        $resRutinas = ActiveRecord::queryArray("
            SELECT COUNT(id) AS total_rutinas
            FROM rutinas
            WHERE entrenador_id = {$entrenadorId} AND cliente_id IS NOT NULL
        ");
        if(!empty($resRutinas[0])) {
            $kpis['totalRutinasAsignadas'] = (int)($resRutinas[0]['total_rutinas'] ?? 0);
        }

        // Listado de clientes con relación al entrenador
        $alumnos = ActiveRecord::queryArray("
            SELECT 
                usuarios.id,
                usuarios.nombre,
                usuarios.apellido,
                usuarios.dni,
                usuarios.email,
                usuarios.telefono,
                usuarios.confirmado,
                COUNT(DISTINCT CASE WHEN horarios.entrenador_id = {$entrenadorId} THEN reservas.id END) AS reservas_con_profesor,
                SUM(CASE WHEN horarios.entrenador_id = {$entrenadorId} AND asistencias.presente = 1 THEN 1 ELSE 0 END) AS asistencias_con_profesor,
                MAX(CASE WHEN horarios.entrenador_id = {$entrenadorId} THEN reservas.fecha END) AS ultima_clase_profesor,
                (SELECT COUNT(r.id) FROM rutinas r WHERE r.cliente_id = usuarios.id AND r.entrenador_id = {$entrenadorId}) AS rutinas_con_profesor,
                GROUP_CONCAT(DISTINCT planes.nombre ORDER BY planes.nombre SEPARATOR ', ') AS planes_activos
            FROM usuarios
            LEFT JOIN reservas ON reservas.usuario_id = usuarios.id
            LEFT JOIN horarios ON horarios.id = reservas.horario_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            LEFT JOIN membresias ON membresias.usuario_id = usuarios.id AND membresias.estado = 'activa'
            LEFT JOIN planes ON planes.id = membresias.plan_id
            WHERE usuarios.rol = 'cliente'
            GROUP BY usuarios.id
            ORDER BY reservas_con_profesor DESC, rutinas_con_profesor DESC, usuarios.nombre ASC
        ");

        $router->render('entrenador/alumnos/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'kpis' => $kpis,
            'alumnos' => $alumnos,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Ficha de seguimiento de un alumno para el entrenador
     */
    public static function detalleAlumno(Router $router): void {
        isEntrenador();

        $entrenadorId = (int)$_SESSION['id'];
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /entrenador/alumnos');
            return;
        }

        $alumno = Usuario::find($id);
        if(!$alumno || $alumno->rol !== 'cliente') {
            header('Location: /entrenador/alumnos');
            return;
        }

        // Estadísticas del alumno con este entrenador
        $resStats = ActiveRecord::queryArray("
            SELECT 
                COUNT(DISTINCT reservas.id) AS total_reservas,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS total_asistencias,
                SUM(CASE WHEN asistencias.presente = 0 THEN 1 ELSE 0 END) AS total_ausencias
            FROM horarios
            INNER JOIN reservas ON reservas.horario_id = horarios.id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE horarios.entrenador_id = {$entrenadorId} AND reservas.usuario_id = {$id}
        ");

        $stats = [
            'totalReservas' => (int)($resStats[0]['total_reservas'] ?? 0),
            'totalAsistencias' => (int)($resStats[0]['total_asistencias'] ?? 0),
            'totalAusencias' => (int)($resStats[0]['total_ausencias'] ?? 0),
            'presentismo' => 100
        ];
        $total = $stats['totalAsistencias'] + $stats['totalAusencias'];
        if($total > 0) {
            $stats['presentismo'] = round(($stats['totalAsistencias'] / $total) * 100);
        }

        // Membresías activas del alumno
        $membresias = ActiveRecord::queryArray("
            SELECT membresias.*, planes.nombre AS plan_nombre
            FROM membresias
            INNER JOIN planes ON planes.id = membresias.plan_id
            WHERE membresias.usuario_id = {$id} AND membresias.estado = 'activa'
            ORDER BY membresias.fecha_inicio DESC
        ");

        // Historial de clases que tomó este alumno con este entrenador
        $clases = ActiveRecord::queryArray("
            SELECT reservas.id, reservas.fecha, reservas.estado AS reserva_estado,
                   horarios.hora_inicio, horarios.hora_fin, horarios.descripcion,
                   planes.nombre AS plan_nombre,
                   asistencias.presente AS asistencia_presente
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            INNER JOIN planes ON planes.id = horarios.plan_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE horarios.entrenador_id = {$entrenadorId} AND reservas.usuario_id = {$id}
            ORDER BY reservas.fecha DESC, horarios.hora_inicio DESC
            LIMIT 50
        ");

        // Rutinas asignadas por este entrenador a este alumno
        $rutinas = ActiveRecord::queryArray("
            SELECT rutinas.id, rutinas.nombre, rutinas.fecha, rutinas.tipo_formato,
                   planes.nombre AS plan_nombre
            FROM rutinas
            INNER JOIN planes ON planes.id = rutinas.plan_id
            WHERE rutinas.entrenador_id = {$entrenadorId} AND rutinas.cliente_id = {$id}
            ORDER BY rutinas.id DESC
        ");

        $router->render('entrenador/alumnos/detalle', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'alumno' => $alumno,
            'stats' => $stats,
            'membresias' => $membresias,
            'clases' => $clases,
            'rutinas' => $rutinas,
            'tipo' => 'dashboard'
        ]);
    }
}
