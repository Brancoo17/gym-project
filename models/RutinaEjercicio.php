<?php

namespace Model;

class RutinaEjercicio extends ActiveRecord {
    protected static string $tabla = 'rutina_ejercicios';
    protected static array $columnasDB = [
        'id', 'rutina_id', 'ejercicio_id', 'bloque', 'rondas', 
        'series', 'reps', 'peso_hombres', 'peso_mujeres', 'dia', 'orden', 'notas'
    ];

    public mixed $rutina_id;
    public mixed $ejercicio_id;
    public string $bloque;
    public mixed $rondas;
    public mixed $series;
    public mixed $reps;
    public ?string $peso_hombres;
    public ?string $peso_mujeres;
    public mixed $dia;
    public mixed $orden;
    public ?string $notas;

    // Propiedades virtuales obtenidas del JOIN con la tabla ejercicios
    public ?string $ejercicio_nombre = '';
    public ?string $grupo_muscular = '';
    public ?string $ejercicio_imagen = '';
    public ?string $video_url = '';
    public ?string $descripcion = '';


    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->rutina_id = $args['rutina_id'] ?? '';
        $this->ejercicio_id = $args['ejercicio_id'] ?? '';
        $this->bloque = $args['bloque'] ?? 'general';
        $this->rondas = !empty($args['rondas']) ? (int)$args['rondas'] : null;
        $this->series = !empty($args['series']) ? (int)$args['series'] : null;
        $this->reps = !empty($args['reps']) ? (int)$args['reps'] : null;
        $this->peso_hombres = !empty($args['peso_hombres']) ? $args['peso_hombres'] : null;
        $this->peso_mujeres = !empty($args['peso_mujeres']) ? $args['peso_mujeres'] : null;
        $this->dia = !empty($args['dia']) ? (int)$args['dia'] : 1;
        $this->orden = !empty($args['orden']) ? (int)$args['orden'] : 0;
        $this->notas = !empty($args['notas']) ? $args['notas'] : null;
        $this->ejercicio_nombre = $args['ejercicio_nombre'] ?? '';
        $this->grupo_muscular = $args['grupo_muscular'] ?? '';
        $this->ejercicio_imagen = $args['ejercicio_imagen'] ?? '';
        $this->video_url = $args['video_url'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
    }

    public function sincronizar($args = []) {
        parent::sincronizar($args);
        $this->rondas = !empty($this->rondas) ? (int)$this->rondas : null;
        $this->series = !empty($this->series) ? (int)$this->series : null;
        $this->reps = !empty($this->reps) ? (int)$this->reps : null;
        $this->peso_hombres = !empty($this->peso_hombres) ? $this->peso_hombres : null;
        $this->peso_mujeres = !empty($this->peso_mujeres) ? $this->peso_mujeres : null;
        $this->dia = !empty($this->dia) ? (int)$this->dia : 1;
        $this->orden = !empty($this->orden) ? (int)$this->orden : 0;
        $this->notas = !empty($this->notas) ? $this->notas : null;
    }

    public function validarRutinaEjercicio(): array {
        if(!$this->ejercicio_id) {
            self::$alertas['error'][] = 'El ejercicio es obligatorio';
        }
        return self::$alertas;
    }
}
