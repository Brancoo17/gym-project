<?php

namespace Model;

class Ejercicio extends ActiveRecord {
    protected static string $tabla = 'ejercicios';
    protected static array $columnasDB = ['id', 'nombre', 'grupo_muscular', 'descripcion', 'imagen', 'video_url'];

    public string $nombre;
    public string $grupo_muscular;
    public string $descripcion;
    public string $imagen;
    public ?string $video_url;

    // Lista estandarizada de grupos musculares
    public const GRUPOS_MUSCULARES = [
        'Pecho',
        'Espalda',
        'Piernas',
        'Hombros',
        'Brazos',
        'Core / Abdomen',
        'Cuerpo Completo / Funcional'
    ];

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->grupo_muscular = $args['grupo_muscular'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->imagen = $args['imagen'] ?? '';
        $this->video_url = $args['video_url'] ?? '';
    }

    public function validarEjercicio(): array {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre del ejercicio es obligatorio';
        }
        if(!$this->grupo_muscular) {
            self::$alertas['error'][] = 'El grupo muscular es obligatorio';
        }
        if(!$this->descripcion) {
            self::$alertas['error'][] = 'La descripción o técnica de ejecución es obligatoria';
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

    public function eliminarImagen(): void {
        if(!$this->imagen) {
            return;
        }

        $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);
        if($existeArchivo) {
            unlink(CARPETA_IMAGENES . $this->imagen);
        }
    }
}
