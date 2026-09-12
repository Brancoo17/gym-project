<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;
use Model\ActiveRecord;

class UsuarioController {

    /**
     * Listado y buscador de clientes para el Administrador
     */
    public static function index(Router $router): void {
        isAdmin();

        $resultado = $_GET['resultado'] ?? null;
        $filtroEstado = $_GET['estado'] ?? 'todos'; // todos, confirmados, pendientes
        $filtroMembresia = $_GET['membresia'] ?? 'todos'; // todos, con_membresia, sin_membresia

        // 1. Estadísticas rápidas de clientes
        $kpis = [
            'total' => 0,
            'confirmados' => 0,
            'pendientes' => 0,
            'conMembresia' => 0,
            'totalAsistencias' => 0
        ];

        $resKpis = ActiveRecord::queryArray("
            SELECT 
                COUNT(id) AS total,
                SUM(CASE WHEN confirmado = 1 THEN 1 ELSE 0 END) AS confirmados,
                SUM(CASE WHEN confirmado = 0 THEN 1 ELSE 0 END) AS pendientes
            FROM usuarios
            WHERE rol = 'cliente'
        ");

        if(!empty($resKpis[0])) {
            $kpis['total'] = (int)($resKpis[0]['total'] ?? 0);
            $kpis['confirmados'] = (int)($resKpis[0]['confirmados'] ?? 0);
            $kpis['pendientes'] = (int)($resKpis[0]['pendientes'] ?? 0);
        }

        $resMem = ActiveRecord::queryArray("
            SELECT COUNT(DISTINCT usuario_id) AS con_membresia
            FROM membresias
            WHERE estado = 'activa' AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())
        ");
        if(!empty($resMem[0])) {
            $kpis['conMembresia'] = (int)($resMem[0]['con_membresia'] ?? 0);
        }

        $resAsist = ActiveRecord::queryArray("
            SELECT COUNT(asistencias.id) AS total_asistencias
            FROM asistencias
            INNER JOIN reservas ON reservas.id = asistencias.reserva_id
            INNER JOIN usuarios ON usuarios.id = reservas.usuario_id
            WHERE usuarios.rol = 'cliente' AND asistencias.presente = 1
        ");
        if(!empty($resAsist[0])) {
            $kpis['totalAsistencias'] = (int)($resAsist[0]['total_asistencias'] ?? 0);
        }

        // 2. Consulta de clientes con sus métricas y membresía
        $clientes = ActiveRecord::queryArray("
            SELECT 
                usuarios.id,
                usuarios.nombre,
                usuarios.apellido,
                usuarios.dni,
                usuarios.email,
                usuarios.telefono,
                usuarios.rol,
                usuarios.confirmado,
                COUNT(DISTINCT reservas.id) AS total_reservas,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS total_asistencias,
                MAX(reservas.fecha) AS ultima_reserva,
                GROUP_CONCAT(DISTINCT planes.nombre SEPARATOR ', ') AS planes_activos
            FROM usuarios
            LEFT JOIN reservas ON reservas.usuario_id = usuarios.id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            LEFT JOIN membresias ON membresias.usuario_id = usuarios.id AND membresias.estado = 'activa' AND (membresias.fecha_fin IS NULL OR membresias.fecha_fin >= CURDATE())
            LEFT JOIN planes ON planes.id = membresias.plan_id
            WHERE usuarios.rol = 'cliente'
            GROUP BY usuarios.id
            ORDER BY usuarios.id DESC
        ");

        $router->render('admin/usuarios/index', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'clientes' => $clientes,
            'kpis' => $kpis,
            'resultado' => $resultado,
            'filtroEstado' => $filtroEstado,
            'filtroMembresia' => $filtroMembresia,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Alternar estado de confirmación de cuenta de un cliente
     */
    public static function toggleConfirmar(): void {
        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if($id) {
                $usuario = Usuario::find($id);
                if($usuario && $usuario->rol === 'cliente') {
                    $usuario->confirmado = ((int)$usuario->confirmado === 1) ? 0 : 1;
                    if((int)$usuario->confirmado === 1) {
                        $usuario->token = ''; // Limpiar token al confirmar manualmente
                    }
                    $usuario->guardar();
                }
            }
        }

        header('Location: /admin/usuarios?resultado=4');
    }

    /**
     * Alta de cliente desde mostrador
     */
    public static function crear(Router $router): void {
        isAdmin();

        $usuario = new Usuario();
        $usuario->rol = 'cliente';
        $usuario->confirmado = 1; // Por defecto activa para altas presenciales en mostrador
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST['usuario'] ?? []);
            $usuario->rol = 'cliente';
            if(!empty($usuario->dni)) {
                $usuario->dni = preg_replace('/[\.\s-]/', '', $usuario->dni);
            }
            $usuario->confirmado = isset($_POST['usuario']['confirmado']) ? (int)$_POST['usuario']['confirmado'] : 1;

            $alertas = $usuario->validarCliente(true);
            $usuario->existeDni();
            $usuario->existeUsuarioExcluyendoId();
            $alertas = Usuario::getAlertas();

            if(empty($alertas)) {
                $usuario->hashPassword();
                if((int)$usuario->confirmado === 1) {
                    $usuario->token = '';
                } else {
                    $usuario->crearToken();
                }

                $resultado = $usuario->guardar();
                if($resultado) {
                    header('Location: /admin/usuarios?resultado=1');
                    return;
                }
            }
        }

        $router->render('admin/usuarios/crear', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'usuario' => $usuario,
            'alertas' => $alertas,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Edición de datos del cliente
     */
    public static function actualizar(Router $router): void {
        isAdmin();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$id) {
            header('Location: /admin/usuarios');
            return;
        }

        $usuario = Usuario::find($id);
        if(!$usuario || $usuario->rol !== 'cliente') {
            header('Location: /admin/usuarios');
            return;
        }

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $passwordAnterior = $usuario->password;
            $usuario->sincronizar($_POST['usuario'] ?? []);
            $usuario->rol = 'cliente';
            if(!empty($usuario->dni)) {
                $usuario->dni = preg_replace('/[\.\s-]/', '', $usuario->dni);
            }
            $usuario->confirmado = isset($_POST['usuario']['confirmado']) ? (int)$_POST['usuario']['confirmado'] : 0;

            $alertas = $usuario->validarCliente(false);
            $usuario->existeDni($id);
            $usuario->existeUsuarioExcluyendoId($id);
            $alertas = Usuario::getAlertas();

            if(empty($alertas)) {
                if(!empty($usuario->password)) {
                    $usuario->hashPassword();
                } else {
                    $usuario->password = $passwordAnterior;
                }

                if((int)$usuario->confirmado === 1) {
                    $usuario->token = '';
                }

                $resultado = $usuario->guardar();
                if($resultado) {
                    header('Location: /admin/usuarios?resultado=2');
                    return;
                }
            }
        }

        $router->render('admin/usuarios/actualizar', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'usuario' => $usuario,
            'alertas' => $alertas,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Ficha detallada y seguimiento del alumno
     */
    public static function detalle(Router $router): void {
        isAdmin();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$id) {
            header('Location: /admin/usuarios');
            return;
        }

        $usuario = Usuario::find($id);
        if(!$usuario || $usuario->rol !== 'cliente') {
            header('Location: /admin/usuarios');
            return;
        }

        // Estadísticas del alumno
        $resKpis = ActiveRecord::queryArray("
            SELECT 
                COUNT(reservas.id) AS total_reservas,
                SUM(CASE WHEN asistencias.presente = 1 THEN 1 ELSE 0 END) AS total_asistencias,
                SUM(CASE WHEN asistencias.presente = 0 THEN 1 ELSE 0 END) AS total_ausencias
            FROM reservas
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE reservas.usuario_id = {$id}
        ");

        $stats = [
            'totalReservas' => (int)($resKpis[0]['total_reservas'] ?? 0),
            'totalAsistencias' => (int)($resKpis[0]['total_asistencias'] ?? 0),
            'totalAusencias' => (int)($resKpis[0]['total_ausencias'] ?? 0),
            'presentismo' => 100
        ];
        $totalTomadas = $stats['totalAsistencias'] + $stats['totalAusencias'];
        if($totalTomadas > 0) {
            $stats['presentismo'] = round(($stats['totalAsistencias'] / $totalTomadas) * 100);
        }

        // Membresías activas (si existen)
        $membresias = ActiveRecord::queryArray("
            SELECT 
                membresias.id,
                membresias.estado,
                membresias.fecha_inicio,
                membresias.fecha_fin,
                planes.nombre AS plan_nombre,
                planes.precio AS plan_precio,
                DATEDIFF(membresias.fecha_fin, CURDATE()) AS dias_restantes
            FROM membresias
            INNER JOIN planes ON planes.id = membresias.plan_id
            WHERE membresias.usuario_id = {$id} AND membresias.estado = 'activa'
        ");

        // Historial de reservas y asistencias
        $reservas = ActiveRecord::queryArray("
            SELECT 
                reservas.id,
                reservas.fecha,
                reservas.estado AS reserva_estado,
                horarios.hora_inicio,
                horarios.hora_fin,
                horarios.descripcion,
                planes.nombre AS plan_nombre,
                CONCAT(entrenadores.nombre, ' ', entrenadores.apellido) AS entrenador_nombre,
                asistencias.presente AS asistencia_presente
            FROM reservas
            INNER JOIN horarios ON horarios.id = reservas.horario_id
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios AS entrenadores ON entrenadores.id = horarios.entrenador_id
            LEFT JOIN asistencias ON asistencias.reserva_id = reservas.id
            WHERE reservas.usuario_id = {$id}
            ORDER BY reservas.fecha DESC, horarios.hora_inicio DESC
            LIMIT 50
        ");

        // Rutinas personalizadas asignadas a este alumno
        $rutinas = ActiveRecord::queryArray("
            SELECT 
                rutinas.id,
                rutinas.nombre,
                rutinas.fecha,
                rutinas.tipo_formato,
                planes.nombre AS plan_nombre,
                CONCAT(entrenadores.nombre, ' ', entrenadores.apellido) AS entrenador_nombre
            FROM rutinas
            INNER JOIN planes ON planes.id = rutinas.plan_id
            INNER JOIN usuarios AS entrenadores ON entrenadores.id = rutinas.entrenador_id
            WHERE rutinas.cliente_id = {$id}
            ORDER BY rutinas.id DESC
        ");

        $router->render('admin/usuarios/detalle', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'usuario' => $usuario,
            'stats' => $stats,
            'membresias' => $membresias,
            'reservas' => $reservas,
            'rutinas' => $rutinas,
            'resultado' => $_GET['resultado'] ?? null,
            'tipo' => 'dashboard'
        ]);
    }

    /**
     * Eliminación de cliente
     */
    public static function eliminar(): void {
        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if($id) {
                $usuario = Usuario::find($id);
                if($usuario && $usuario->rol === 'cliente') {
                    $usuario->eliminar();
                    header('Location: /admin/usuarios?resultado=3');
                    return;
                }
            }
        }

        header('Location: /admin/usuarios');
    }

    /**
     * Pago manual en efectivo de membresía
     */
    public static function membresiaManual(Router $router): void {
        isAdmin();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$id) {
            header('Location: /admin/usuarios');
            return;
        }

        $usuario = Usuario::find($id);
        if(!$usuario || $usuario->rol !== 'cliente') {
            header('Location: /admin/usuarios');
            return;
        }

        $alertas = [];
        $planes = \Model\Plan::whereAll('activo', 1);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $planId = filter_var($_POST['plan_id'] ?? null, FILTER_VALIDATE_INT);
            $fechaInicio = filter_var($_POST['fecha_inicio'] ?? date('Y-m-d'), FILTER_SANITIZE_SPECIAL_CHARS);
            
            if(!$planId) {
                $alertas['error'][] = 'Debés seleccionar un plan.';
            }

            if(empty($alertas)) {
                $plan = \Model\Plan::find($planId);
                $dias = (int)$plan->duracion_dias;
                $fechaFin = date('Y-m-d', strtotime($fechaInicio . " + {$dias} days"));
                
                // 1. Crear y guardar la membresía activa primero
                $membresia = new \Model\Membresia([
                    'usuario_id' => $usuario->id,
                    'plan_id' => $plan->id,
                    'estado' => 'activa',
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]);
                $resultadoMembresia = $membresia->guardar();

                if($resultadoMembresia && !empty($resultadoMembresia['id'])) {
                    // 2. Generar pago en efectivo vinculado a la membresía
                    $pago = new \Model\Pago([
                        'membresia_id' => (int)$resultadoMembresia['id'],
                        'monto' => $plan->precio,
                        'estado' => 'aprobado',
                        'mp_status' => 'efectivo'
                    ]);
                    $pago->guardar();

                    header('Location: /admin/usuarios/detalle?id=' . $usuario->id . '&resultado=5');
                    return;
                }
            }
        }

        $router->render('admin/usuarios/membresia-manual', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'usuario' => $usuario,
            'planes' => $planes,
            'alertas' => $alertas,
            'tipo' => 'dashboard'
        ]);
    }
}
