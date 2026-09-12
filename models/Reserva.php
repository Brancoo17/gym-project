<?php

namespace Model;

class Reserva extends ActiveRecord {
    protected static string $tabla = 'reservas';
    protected static array $columnasDB = ['id', 'usuario_id', 'horario_id', 'fecha', 'estado'];

    public mixed $usuario_id;
    public mixed $horario_id;
    public string $fecha;
    public string $estado;

    // Propiedades adicionales para joins y vistas
    public string $plan_nombre = '';
    public string $entrenador = '';
    public string $hora_inicio = '';
    public string $hora_fin = '';
    public mixed $dia_semana = '';
    public string $cliente_nombre = '';
    public string $cliente_email = '';
    public ?string $cliente_telefono = '';
    public mixed $cupo_total = '';
    public mixed $cupo_disponible = '';
    public mixed $asistencia_id = null;
    public mixed $asistencia_presente = null;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->horario_id = $args['horario_id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->estado = $args['estado'] ?? 'reservada';
        $this->plan_nombre = $args['plan_nombre'] ?? '';
        $this->entrenador = $args['entrenador'] ?? '';
        $this->hora_inicio = $args['hora_inicio'] ?? '';
        $this->hora_fin = $args['hora_fin'] ?? '';
        $this->dia_semana = $args['dia_semana'] ?? '';
        $this->cliente_nombre = $args['cliente_nombre'] ?? '';
        $this->cliente_email = $args['cliente_email'] ?? '';
        $this->cliente_telefono = $args['cliente_telefono'] ?? '';
        $this->cupo_total = $args['cupo_total'] ?? '';
        $this->cupo_disponible = $args['cupo_disponible'] ?? '';
        $this->asistencia_id = $args['asistencia_id'] ?? null;
        $this->asistencia_presente = $args['asistencia_presente'] ?? null;
    }

    public function validarReserva(): array {
        if(!$this->usuario_id) {
            self::$alertas['error'][] = 'El usuario es obligatorio';
        }
        if(!$this->horario_id) {
            self::$alertas['error'][] = 'El horario es obligatorio';
        }
        if(!$this->fecha) {
            self::$alertas['error'][] = 'La fecha es obligatoria';
        } elseif($this->fecha < date('Y-m-d')) {
            self::$alertas['error'][] = 'No se pueden reservar turnos en fechas pasadas';
        }

        return self::$alertas;
    }

    /**
     * Calcula la cantidad de cupos disponibles para un horario en una fecha dada.
     * Si cupoTotal es null, retorna null indicando cupo ilimitado.
     */
    public static function cuposDisponibles(int $horarioId, string $fecha, ?int $cupoTotal): ?int {
        if(is_null($cupoTotal)) {
            return null;
        }

        $fechaSegura = self::$db->escape_string($fecha);
        $query = "SELECT COUNT(*) as total FROM " . static::$tabla . " 
                  WHERE horario_id = {$horarioId} 
                  AND fecha = '{$fechaSegura}' 
                  AND estado = 'reservada'";
        $resultado = self::$db->query($query);
        $fila = $resultado->fetch_assoc();
        $reservasActivas = (int)($fila['total'] ?? 0);

        return max(0, $cupoTotal - $reservasActivas);
    }

    /**
     * Comprueba si el usuario ya tiene una reserva activa para ese horario y fecha
     */
    public static function existeReservaUsuario(int $usuarioId, int $horarioId, string $fecha): ?self {
        $fechaSegura = self::$db->escape_string($fecha);
        $query = "SELECT * FROM " . static::$tabla . " 
                  WHERE usuario_id = {$usuarioId} 
                  AND horario_id = {$horarioId} 
                  AND fecha = '{$fechaSegura}' 
                  AND estado = 'reservada' 
                  LIMIT 1";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }
}
