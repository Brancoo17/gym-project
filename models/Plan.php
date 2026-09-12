<?php

namespace Model;

class Plan extends ActiveRecord {
    protected static string $tabla = 'planes';
    protected static array $columnasDB = ['id', 'nombre', 'tipo_disciplina', 'descripcion', 'precio', 'duracion_dias', 'cantidad_clases', 'imagen', 'activo'];

    public string $nombre;
    public string $tipo_disciplina;
    public string $descripcion;
    public mixed $precio;
    public mixed $duracion_dias;
    public mixed $cantidad_clases;
    public string $imagen;
    public mixed $activo;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->tipo_disciplina = $args['tipo_disciplina'] ?? 'musculacion';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->precio = $args['precio'] ?? '';
        $this->duracion_dias = $args['duracion_dias'] ?? '';
        $this->cantidad_clases = !empty($args['cantidad_clases']) ? (int)$args['cantidad_clases'] : null;
        $this->imagen = $args['imagen'] ?? '';
        $this->activo = $args['activo'] ?? 1;
    }

    public function validarPlan() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->descripcion) {
            self::$alertas['error'][] = 'La descripción es obligatoria';
        }
        if($this->precio === '' || $this->precio === null) {
            self::$alertas['error'][] = 'El precio es obligatorio';
        }
        if(!$this->duracion_dias) {
            self::$alertas['error'][] = 'La duración en días es obligatoria';
        }
        if(!$this->imagen) {
            self::$alertas['error'][] = 'La imagen es obligatoria';
        }
        return self::$alertas;
    }

    public function setImagen(string $imagen): void {
        if(!is_null($this->id)) {
            $this->eliminarImagen();
        }

        if($imagen) {
            $this->imagen = $imagen;
        }
    }

    public function eliminarImagen() {
        if(!$this->imagen) {
            return;
        }
        $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);
        if($existeArchivo) {
            unlink(CARPETA_IMAGENES . $this->imagen);
        }
    }
}
