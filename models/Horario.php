<?php

namespace Model;

class Horario extends ActiveRecord {
    protected static string $tabla = 'horarios';
    protected static array $columnasDB = ['id', 'plan_id', 'entrenador_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'cupo', 'descripcion'];

    public mixed $plan_id;
    public mixed $entrenador_id;
    public mixed $dia_semana;
    public string $hora_inicio;
    public string $hora_fin;
    public mixed $cupo;
    public ?string $descripcion;
    public string $plan_nombre = '';
    public ?string $plan_imagen = '';
    public string $entrenador = '';

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->plan_id = $args['plan_id'] ?? null;
        $this->entrenador_id = $args['entrenador_id'] ?? null;
        $this->dia_semana = $args['dia_semana'] ?? '';
        $this->hora_inicio = $args['hora_inicio'] ?? '';
        $this->hora_fin = $args['hora_fin'] ?? '';
        $this->cupo = (isset($args['cupo']) && $args['cupo'] !== '') ? $args['cupo'] : null;
        $this->descripcion = $args['descripcion'] ?? '';
        $this->plan_nombre = $args['plan_nombre'] ?? '';
        $this->plan_imagen = $args['plan_imagen'] ?? '';
        $this->entrenador = $args['entrenador'] ?? '';
    }

    public function validarHorario(): array {
        if(!$this->plan_id) {
            self::$alertas['error'][] = 'El plan es obligatorio';
        }
        if(!$this->entrenador_id) {
            self::$alertas['error'][] = 'El entrenador es obligatorio';
        }
        if($this->dia_semana === '' || !is_numeric($this->dia_semana) || $this->dia_semana < 0 || $this->dia_semana > 6) {
            self::$alertas['error'][] = 'El día de la semana es obligatorio y debe ser válido';
        }
        if(!$this->hora_inicio) {
            self::$alertas['error'][] = 'La hora de inicio es obligatoria';
        }
        if(!$this->hora_fin) {
            self::$alertas['error'][] = 'La hora de fin es obligatoria';
        }
        if($this->hora_inicio && $this->hora_fin && $this->hora_inicio >= $this->hora_fin) {
            self::$alertas['error'][] = 'La hora de fin debe ser posterior a la hora de inicio';
        }
        if($this->cupo !== null && $this->cupo !== '') {
            if(!is_numeric($this->cupo) || (int)$this->cupo <= 0) {
                self::$alertas['error'][] = 'El cupo debe ser un número mayor a cero';
            }
        }

        return self::$alertas;
    }
}
