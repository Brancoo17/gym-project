<?php

namespace Model;

class Asistencia extends ActiveRecord {
    protected static string $tabla = 'asistencias';
    protected static array $columnasDB = ['id', 'reserva_id', 'presente', 'marcado_por'];

    public mixed $reserva_id;
    public mixed $presente;
    public mixed $marcado_por;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->reserva_id = $args['reserva_id'] ?? null;
        $this->presente = isset($args['presente']) ? (int)$args['presente'] : 0;
        $this->marcado_por = $args['marcado_por'] ?? null;
    }

    public function validarAsistencia(): array {
        if(!$this->reserva_id || !is_numeric($this->reserva_id)) {
            self::$alertas['error'][] = 'La reserva es obligatoria';
        }
        if(!$this->marcado_por || !is_numeric($this->marcado_por)) {
            self::$alertas['error'][] = 'El usuario que marca la asistencia es obligatorio';
        }

        return self::$alertas;
    }

    /**
     * Busca la asistencia vinculada a una reserva
     */
    public static function porReserva(int $reservaId): ?self {
        return self::where('reserva_id', $reservaId);
    }
}
