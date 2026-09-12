<?php

namespace Controllers;

use Model\Horario;
use Model\Reserva;
use Model\Plan;
use Model\Asistencia;

class APIController {

    /**
     * Devuelve los horarios de un día con cálculo de cupos y reservas del usuario
     */
    public static function horarios(): void {
        header('Content-Type: application/json');

        if(!isset($_SESSION['login'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $fecha = filter_var($_GET['fecha'] ?? date('Y-m-d'), FILTER_SANITIZE_SPECIAL_CHARS);

        if(!$fecha || !strtotime($fecha)) {
            http_response_code(400);
            echo json_encode(['error' => 'Fecha no válida']);
            return;
        }

        // Obtener día de la semana (0 = Domingo, 1 = Lunes, ..., 6 = Sábado)
        $diaSemana = (int)date('w', strtotime($fecha));

        $usuarioId = (int)($_SESSION['id'] ?? 0);

        $horarios = Horario::SQL("
            SELECT horarios.*, planes.nombre AS plan_nombre, planes.tipo_disciplina, planes.imagen AS plan_imagen,
                   CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS entrenador
            FROM horarios
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios ON usuarios.id = horarios.entrenador_id
            INNER JOIN membresias ON membresias.plan_id = planes.id
            WHERE horarios.dia_semana = {$diaSemana} 
              AND planes.activo = 1 
              AND membresias.usuario_id = {$usuarioId}
              AND membresias.estado = 'activa'
            ORDER BY horarios.hora_inicio ASC
        ");

        $respuesta = [];
        $hoy = date('Y-m-d');
        $horaActual = date('H:i:s');

        foreach($horarios as $h) {
            $esIlimitado = is_null($h->cupo) || $h->cupo === '';
            $cupoTotal = $esIlimitado ? null : (int)$h->cupo;
            $cupoDisponible = Reserva::cuposDisponibles((int)$h->id, $fecha, $cupoTotal);
            $reservaUsuario = Reserva::existeReservaUsuario($usuarioId, (int)$h->id, $fecha);
            $esCorrido = (($h->tipo_disciplina ?? '') === 'musculacion') 
                         || stripos($h->plan_nombre, 'musculaci') !== false 
                         || (strtotime($h->hora_fin) - strtotime($h->hora_inicio) > 5400);

            if($esCorrido) {
                // En turnos corridos (musculación) se permite reservar hasta 1 hora antes de la finalización del turno
                $horaLimite = date('H:i:s', strtotime($h->hora_fin . ' -1 hour'));
                $esPasado = ($fecha < $hoy) || ($fecha === $hoy && $horaActual > $horaLimite);
            } else {
                $esPasado = ($fecha < $hoy) || ($fecha === $hoy && $h->hora_inicio <= $horaActual);
            }

            $respuesta[] = [
                'id' => (int)$h->id,
                'plan_id' => (int)$h->plan_id,
                'plan_nombre' => $h->plan_nombre,
                'tipo_disciplina' => $h->tipo_disciplina ?? 'musculacion',
                'plan_imagen' => $h->plan_imagen,
                'entrenador' => $h->entrenador,
                'dia_semana' => (int)$h->dia_semana,
                'hora_inicio' => substr($h->hora_inicio, 0, 5),
                'hora_fin' => substr($h->hora_fin, 0, 5),
                'cupo_total' => $cupoTotal,
                'cupo_disponible' => $cupoDisponible,
                'descripcion' => $h->descripcion ?? '',
                'ilimitado' => $esIlimitado,
                'agotado' => !$esIlimitado && ($cupoDisponible <= 0),
                'ya_reservado' => !is_null($reservaUsuario),
                'reserva_id' => $reservaUsuario ? (int)$reservaUsuario->id : null,
                'pasado' => $esPasado
            ];
        }

        echo json_encode($respuesta);
    }

    /**
     * Procesa la reserva de un turno para el usuario logueado
     */
    public static function reservar(): void {
        header('Content-Type: application/json');

        if(!isset($_SESSION['login'])) {
            http_response_code(401);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Debes iniciar sesión para reservar']);
            return;
        }

        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Método no permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $horarioId = filter_var($input['horario_id'] ?? null, FILTER_VALIDATE_INT);
        $fecha = filter_var($input['fecha'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $usuarioId = (int)$_SESSION['id'];

        if(!$horarioId || !$fecha) {
            http_response_code(400);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Datos incompletos para reservar']);
            return;
        }

        // Validar que la fecha y horario no hayan pasado
        $hoy = date('Y-m-d');
        $horaActual = date('H:i:s');
        if($fecha < $hoy) {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'No podés reservar turnos en fechas pasadas']);
            return;
        }

        // Buscar el horario
        $horario = Horario::find($horarioId);
        if(!$horario) {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'El horario seleccionado no existe']);
            return;
        }

        $plan = Plan::find($horario->plan_id);

        // Validar que el usuario tenga membresía activa para este plan
        $membresiaActiva = \Model\ActiveRecord::queryArray("
            SELECT id FROM membresias 
            WHERE usuario_id = {$usuarioId} 
            AND plan_id = {$horario->plan_id} 
            AND estado = 'activa'
        ");
        if(empty($membresiaActiva)) {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'No tenés membresía activa para este plan.']);
            return;
        }

        // Validar límite de clases mensuales del plan
        if(!empty($plan->cantidad_clases)) {
            $mesActual = date('Y-m', strtotime($fecha)); // Mes de la reserva
            $reservasMes = \Model\ActiveRecord::queryArray("
                SELECT COUNT(*) as total 
                FROM reservas r
                INNER JOIN horarios h ON h.id = r.horario_id
                WHERE r.usuario_id = {$usuarioId} 
                AND h.plan_id = {$plan->id} 
                AND r.estado != 'cancelada'
                AND DATE_FORMAT(r.fecha, '%Y-%m') = '{$mesActual}'
            ");
            $totalMes = (int)($reservasMes[0]['total'] ?? 0);
            if($totalMes >= (int)$plan->cantidad_clases) {
                echo json_encode(['tipo' => 'error', 'mensaje' => 'Ya alcanzaste el límite de ' . $plan->cantidad_clases . ' clases mensuales para este plan.']);
                return;
            }
        }

        // Validar que el turno de hoy no haya pasado el horario límite
        $esCorrido = (($plan->tipo_disciplina ?? '') === 'musculacion') 
                     || stripos($plan->nombre ?? '', 'musculaci') !== false 
                     || (strtotime($horario->hora_fin) - strtotime($horario->hora_inicio) > 5400);

        if($fecha === $hoy) {
            if($esCorrido) {
                $horaLimite = date('H:i:s', strtotime($horario->hora_fin . ' -1 hour'));
                if($horaActual > $horaLimite) {
                    echo json_encode([
                        'tipo' => 'error', 
                        'mensaje' => 'Los turnos de musculación y horario corrido solo pueden reservarse hasta 1 hora antes de su finalización (' . substr($horaLimite, 0, 5) . ' hs)'
                    ]);
                    return;
                }
            } else {
                if($horario->hora_inicio <= $horaActual) {
                    echo json_encode(['tipo' => 'error', 'mensaje' => 'No podés reservar un turno que ya ha comenzado o finalizado']);
                    return;
                }
            }
        }

        // Validar que el día de la semana coincida
        $diaSemanaFecha = (int)date('w', strtotime($fecha));
        if($diaSemanaFecha !== (int)$horario->dia_semana) {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'La fecha seleccionada no corresponde al día de esta clase']);
            return;
        }

        // Validar si ya tiene reserva activa
        $reservaExistente = Reserva::existeReservaUsuario($usuarioId, $horarioId, $fecha);
        if($reservaExistente) {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Ya tenés una reserva activa para este turno']);
            return;
        }

        // Validar cupos disponibles si no es ilimitado
        if(!is_null($horario->cupo) && $horario->cupo !== '') {
            $cupoDisponible = Reserva::cuposDisponibles($horarioId, $fecha, (int)$horario->cupo);
            if($cupoDisponible <= 0) {
                echo json_encode(['tipo' => 'error', 'mensaje' => 'El cupo para este turno está agotado']);
                return;
            }
        }

        // Si existe un registro cancelado previo (por la clave UNIQUE usuario, horario, fecha), reactivarlo
        $reservaCancelada = Reserva::SQL("
            SELECT * FROM reservas 
            WHERE usuario_id = {$usuarioId} 
            AND horario_id = {$horarioId} 
            AND fecha = '{$fecha}' 
            LIMIT 1
        ");
        $reservaCancelada = array_shift($reservaCancelada);

        if($reservaCancelada) {
            $reservaCancelada->estado = 'reservada';
            $resultado = $reservaCancelada->guardar();
            $reservaId = $reservaCancelada->id;
        } else {
            $nuevaReserva = new Reserva([
                'usuario_id' => $usuarioId,
                'horario_id' => $horarioId,
                'fecha' => $fecha,
                'estado' => 'reservada'
            ]);
            $resultado = $nuevaReserva->guardar();
            $reservaId = $resultado['id'] ?? null;
        }

        if($resultado) {
            echo json_encode([
                'tipo' => 'exito',
                'mensaje' => '¡Turno reservado con éxito!',
                'reserva_id' => $reservaId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Hubo un problema al procesar tu reserva']);
        }
    }

    /**
     * Cancela una reserva activa
     */
    public static function cancelar(): void {
        header('Content-Type: application/json');

        if(!isset($_SESSION['login'])) {
            http_response_code(401);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Debes iniciar sesión']);
            return;
        }

        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Método no permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $reservaId = filter_var($input['reserva_id'] ?? null, FILTER_VALIDATE_INT);

        if(!$reservaId) {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'ID de reserva no válido']);
            return;
        }

        $reserva = Reserva::find($reservaId);
        if(!$reserva) {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'La reserva no existe']);
            return;
        }

        // Solo el dueño de la reserva o un admin puede cancelarla
        $esDuenio = (int)$reserva->usuario_id === (int)$_SESSION['id'];
        $esAdmin = ($_SESSION['rol'] ?? '') === 'admin';

        if(!$esDuenio && !$esAdmin) {
            http_response_code(403);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'No tenés permisos para cancelar esta reserva']);
            return;
        }

        // Validar que el turno no haya comenzado ni pasado (para clientes)
        $hoy = date('Y-m-d');
        $horaActual = date('H:i:s');
        $horario = Horario::find((int)$reserva->horario_id);
        $plan = $horario ? Plan::find($horario->plan_id) : null;
        $esCorrido = $plan && ((($plan->tipo_disciplina ?? '') === 'musculacion') 
                     || stripos($plan->nombre ?? '', 'musculaci') !== false 
                     || ($horario && strtotime($horario->hora_fin) - strtotime($horario->hora_inicio) > 5400));

        $horaLimite = ($horario && $esCorrido) 
                      ? date('H:i:s', strtotime($horario->hora_fin . ' -1 hour')) 
                      : ($horario ? $horario->hora_inicio : '00:00:00');

        $turnoPasado = ($reserva->fecha < $hoy) || ($horario && $reserva->fecha === $hoy && $horaActual > $horaLimite);

        if($turnoPasado && !$esAdmin) {
            echo json_encode([
                'tipo' => 'error',
                'mensaje' => 'No podés cancelar un turno que ya ha comenzado o finalizado.'
            ]);
            return;
        }

        // Si la asistencia ya fue confirmada como Presente, no se puede cancelar (a menos que sea admin)
        $asistencia = Asistencia::porReserva((int)$reserva->id);
        if($asistencia && (int)$asistencia->presente === 1 && !$esAdmin) {
            echo json_encode([
                'tipo' => 'error',
                'mensaje' => 'No podés cancelar este turno porque tu asistencia ya fue confirmada por el profesor.'
            ]);
            return;
        }

        // Marcar como cancelada para liberar el cupo
        $reserva->estado = 'cancelada';
        $resultado = $reserva->guardar();

        if($resultado) {
            echo json_encode([
                'tipo' => 'exito',
                'mensaje' => 'Reserva cancelada correctamente. El cupo ha sido liberado.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'No se pudo cancelar la reserva']);
        }
    }

    /**
     * Devuelve los horarios semanales asignados a un entrenador
     */
    public static function horariosEntrenador(): void {
        header('Content-Type: application/json');

        if(!isset($_SESSION['login']) || ($_SESSION['rol'] ?? '') !== 'admin') {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $entrenadorId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if(!$entrenadorId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de entrenador no válido']);
            return;
        }

        $horarios = Horario::SQL("
            SELECT horarios.*, planes.nombre AS plan_nombre
            FROM horarios
            INNER JOIN planes ON planes.id = horarios.plan_id
            WHERE horarios.entrenador_id = {$entrenadorId}
            ORDER BY horarios.dia_semana ASC, horarios.hora_inicio ASC
        ");

        $diasSemana = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];

        $resultado = [];
        foreach($horarios as $h) {
            $resultado[] = [
                'id' => (int)$h->id,
                'dia_num' => (int)$h->dia_semana,
                'dia_nombre' => $diasSemana[(int)$h->dia_semana] ?? 'Desconocido',
                'hora_inicio' => substr($h->hora_inicio, 0, 5),
                'hora_fin' => substr($h->hora_fin, 0, 5),
                'plan_nombre' => $h->plan_nombre,
                'cupo' => (!is_null($h->cupo) && $h->cupo !== '') ? (int)$h->cupo : null,
                'descripcion' => $h->descripcion ?? ''
            ];
        }

        echo json_encode($resultado);
    }

    /**
     * Marca o desmarca la asistencia de un alumno para una reserva
     */
    public static function marcarAsistencia(): void {
        header('Content-Type: application/json');

        if(!isset($_SESSION['login'])) {
            http_response_code(401);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Debes iniciar sesión para realizar esta acción']);
            return;
        }

        $rol = $_SESSION['rol'] ?? '';
        if($rol !== 'admin' && $rol !== 'entrenador') {
            http_response_code(403);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'No tienes permisos para registrar asistencia']);
            return;
        }

        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Método no permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $reservaId = filter_var($input['reserva_id'] ?? null, FILTER_VALIDATE_INT);
        $presente = isset($input['presente']) ? (int)(bool)$input['presente'] : null;

        if(!$reservaId || is_null($presente)) {
            http_response_code(400);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Datos incompletos para registrar la asistencia']);
            return;
        }

        // Buscar la reserva
        $reserva = Reserva::find($reservaId);
        if(!$reserva) {
            http_response_code(404);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'La reserva especificada no existe']);
            return;
        }

        if($reserva->estado !== 'reservada') {
            echo json_encode(['tipo' => 'error', 'mensaje' => 'No se puede marcar asistencia en una reserva cancelada']);
            return;
        }

        $usuarioId = (int)$_SESSION['id'];

        // Si es entrenador, validar que sea el entrenador asignado al horario de esa reserva (o permitir si admin)
        if($rol === 'entrenador') {
            $horario = Horario::find((int)$reserva->horario_id);
            if(!$horario || (int)$horario->entrenador_id !== $usuarioId) {
                http_response_code(403);
                echo json_encode(['tipo' => 'error', 'mensaje' => 'Solo el entrenador a cargo de la clase o un administrador puede registrar asistencia']);
                return;
            }
        }

        // Buscar si ya existe registro de asistencia para esta reserva
        $asistencia = Asistencia::porReserva($reservaId);

        if($asistencia) {
            $asistencia->presente = $presente;
            $asistencia->marcado_por = $usuarioId;
            $resultado = $asistencia->guardar();
        } else {
            $asistencia = new Asistencia([
                'reserva_id' => $reservaId,
                'presente' => $presente,
                'marcado_por' => $usuarioId
            ]);
            $resultado = $asistencia->guardar();
        }

        if($resultado) {
            echo json_encode([
                'tipo' => 'exito',
                'mensaje' => $presente ? 'Asistencia confirmada' : 'Ausencia registrada',
                'reserva_id' => $reservaId,
                'presente' => (int)$asistencia->presente,
                'asistencia_id' => $asistencia->id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['tipo' => 'error', 'mensaje' => 'Hubo un error al guardar la asistencia']);
        }
    }
}

