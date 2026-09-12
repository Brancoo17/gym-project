<?php

namespace Model;

class Pago extends ActiveRecord {
    protected static string $tabla = 'pagos';
    protected static array $columnasDB = ['id', 'membresia_id', 'monto', 'estado', 'mp_preference_id', 'mp_payment_id', 'mp_status'];

    public ?int $id;
    public mixed $membresia_id;
    public mixed $monto;
    public string $estado;
    public ?string $mp_preference_id;
    public ?string $mp_payment_id;
    public ?string $mp_status;

    public function __construct(array $args = []) {
        $this->id = $args['id'] ?? null;
        $this->membresia_id = $args['membresia_id'] ?? '';
        $this->monto = $args['monto'] ?? '';
        $this->estado = $args['estado'] ?? 'pendiente';
        $this->mp_preference_id = $args['mp_preference_id'] ?? null;
        $this->mp_payment_id = $args['mp_payment_id'] ?? null;
        $this->mp_status = $args['mp_status'] ?? null;
    }

    public function validar(): array {
        self::$alertas = [];

        if(!$this->membresia_id) {
            self::$alertas['error'][] = 'El ID de la membresía es obligatorio';
        }
        if(!$this->monto) {
            self::$alertas['error'][] = 'El monto del pago es obligatorio';
        }

        return self::$alertas;
    }
}
