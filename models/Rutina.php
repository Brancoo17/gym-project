<?php

namespace Model;

class Rutina extends ActiveRecord {
    protected static string $tabla = 'rutinas';
    protected static array $columnasDB = [
        'id', 'plan_id', 'cliente_id', 'entrenador_id', 'fecha', 
        'tipo_formato', 'wod_formato', 'wod_tiempo', 'wod_descripcion', 'nombre'
    ];

    public mixed $plan_id;
    public mixed $cliente_id;
    public mixed $entrenador_id;
    public ?string $fecha;
    public string $tipo_formato;
    public ?string $wod_formato;
    public ?string $wod_tiempo;
    public ?string $wod_descripcion;
    public ?string $nombre = '';

    // Propiedades virtuales para joins y agregados
    public ?string $plan_nombre = '';
    public ?string $cliente_nombre = '';
    public ?string $cliente_apellido = '';
    public ?string $cliente_email = '';
    public ?string $entrenador_nombre = '';
    public ?string $entrenador_apellido = '';
    public mixed $total_ejercicios = 0;
    public mixed $total_bloques = 0;


    // Constantes de formatos de WOD para Crossfit
    public const FORMATOS_WOD = [
        'for_time' => 'For Time (Por Tiempo)',
        'amrap'    => 'AMRAP (As Many Rounds As Possible)',
        'emom'     => 'EMOM (Every Minute On the Minute)',
        'tabata'   => 'TABATA (20s trabajo / 10s descanso)',
        'chipper'  => 'Chipper (Completar la secuencia)',
        'ladder'   => 'Ladder (Escalera de reps)'
    ];

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->plan_id = $args['plan_id'] ?? '';
        $this->cliente_id = !empty($args['cliente_id']) ? (int)$args['cliente_id'] : null;
        $this->entrenador_id = $args['entrenador_id'] ?? '';
        $this->fecha = $args['fecha'] ?? date('Y-m-d');
        $this->tipo_formato = $args['tipo_formato'] ?? 'estandar';
        $this->wod_formato = !empty($args['wod_formato']) ? $args['wod_formato'] : null;
        $this->wod_tiempo = !empty($args['wod_tiempo']) ? $args['wod_tiempo'] : null;
        $this->wod_descripcion = !empty($args['wod_descripcion']) ? $args['wod_descripcion'] : null;
        $this->nombre = $args['nombre'] ?? '';
    }

    public function sincronizar($args = []) {
        parent::sincronizar($args);
        $this->cliente_id = !empty($this->cliente_id) ? (int)$this->cliente_id : null;
        if(empty($this->wod_formato)) {
            $this->wod_formato = null;
        }
        if(empty($this->wod_tiempo)) {
            $this->wod_tiempo = null;
        }
        if(empty($this->wod_descripcion)) {
            $this->wod_descripcion = null;
        }
    }

    public function validarRutina(): array {
        if(!$this->plan_id) {
            self::$alertas['error'][] = 'El plan o disciplina es obligatorio';
        }
        if(!$this->entrenador_id) {
            self::$alertas['error'][] = 'El entrenador a cargo es obligatorio';
        }
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre o título de la rutina es obligatorio';
        }
        if(!$this->fecha) {
            self::$alertas['error'][] = 'La fecha de la rutina es obligatoria';
        }
        if($this->tipo_formato === 'crossfit' && !$this->wod_formato) {
            self::$alertas['error'][] = 'Debés seleccionar el formato de WOD para la rutina de Crossfit';
        }
        return self::$alertas;
    }

    public function esCrossfit(): bool {
        return $this->tipo_formato === 'crossfit';
    }

    public function getNombreFormatoWod(): string {
        return self::FORMATOS_WOD[$this->wod_formato] ?? ($this->wod_formato ?: 'Estándar');
    }
}
