<?php

namespace Model;

class Membresia extends ActiveRecord {
    protected static string $tabla = 'membresias';
    protected static array $columnasDB = ['id', 'usuario_id', 'plan_id', 'estado', 'fecha_inicio', 'fecha_fin'];

    public ?int $id;
    public mixed $usuario_id;
    public mixed $plan_id;
    public string $estado;
    public ?string $fecha_inicio;
    public ?string $fecha_fin;

    public function __construct(array $args = []) {
        $this->id = $args['id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? '';
        $this->plan_id = $args['plan_id'] ?? '';
        $this->estado = $args['estado'] ?? 'pendiente';
        $this->fecha_inicio = $args['fecha_inicio'] ?? null;
        $this->fecha_fin = $args['fecha_fin'] ?? null;
    }

    public function validar(): array {
        self::$alertas = [];

        if(!$this->usuario_id) {
            self::$alertas['error'][] = 'El usuario es obligatorio';
        }
        if(!$this->plan_id) {
            self::$alertas['error'][] = 'El plan es obligatorio';
        }

        return self::$alertas;
    }
}
