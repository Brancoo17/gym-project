<?php

namespace Model;

class Configuracion extends ActiveRecord {
    protected static string $tabla = 'configuracion';
    protected static array $columnasDB = [
        'id', 'nombre', 'logo', 'portada', 'email', 
        'whatsapp', 'instagram', 'facebook', 'tiktok',
        'direccion', 'mapa_url', 'hero_titulo', 'hero_descripcion',
        'habilitar_turnos', 'habilitar_crossfit'
    ];

    public ?string $nombre = 'GYM';
    public ?string $logo = '';
    public ?string $portada = '';
    public ?string $email = 'admin@gym.com';
    public ?string $whatsapp = '';
    public ?string $instagram = '';
    public ?string $facebook = '';
    public ?string $tiktok = '';
    public ?string $direccion = 'Av. del Entrenamiento 123, Buenos Aires';
    public ?string $mapa_url = '';
    public ?string $hero_titulo = 'Entrená con un plan a tu medida';
    public ?string $hero_descripcion = 'Crossfit, musculación y funcional. Elegí tu membresía y reservá tu turno.';
    public mixed $habilitar_turnos = 1;
    public mixed $habilitar_crossfit = 1;

    public function __construct(array $args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = trim($args['nombre'] ?? 'GYM');
        $this->logo = $args['logo'] ?? '';
        $this->portada = $args['portada'] ?? '';
        $this->email = trim($args['email'] ?? 'admin@gym.com');
        $this->whatsapp = trim($args['whatsapp'] ?? '');
        $this->instagram = trim($args['instagram'] ?? '');
        $this->facebook = trim($args['facebook'] ?? '');
        $this->tiktok = trim($args['tiktok'] ?? '');
        $this->direccion = trim($args['direccion'] ?? 'Av. del Entrenamiento 123, Buenos Aires');
        $this->mapa_url = trim($args['mapa_url'] ?? '');
        $this->hero_titulo = trim($args['hero_titulo'] ?? 'Entrená con un plan a tu medida');
        $this->hero_descripcion = trim($args['hero_descripcion'] ?? 'Crossfit, musculación y funcional. Elegí tu membresía y reservá tu turno.');
        $this->habilitar_turnos = isset($args['habilitar_turnos']) ? (int)$args['habilitar_turnos'] : 1;
        $this->habilitar_crossfit = isset($args['habilitar_crossfit']) ? (int)$args['habilitar_crossfit'] : 1;
    }

    public function validar(): array {
        self::$alertas = [];

        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre del establecimiento es obligatorio';
        }

        if(strlen($this->nombre) > 60) {
            self::$alertas['error'][] = 'El nombre no puede exceder los 60 caracteres';
        }

        if(!$this->email) {
            self::$alertas['error'][] = 'El email de contacto es obligatorio';
        } elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'El formato del email de contacto no es válido';
        }

        return self::$alertas;
    }

    public function setLogo(string $logo): void {
        if(!is_null($this->id) && !empty($this->logo)) {
            $this->eliminarArchivo($this->logo);
        }

        if($logo) {
            $this->logo = $logo;
        }
    }

    public function setPortada(string $portada): void {
        if(!is_null($this->id) && !empty($this->portada)) {
            $this->eliminarArchivo($this->portada);
        }

        if($portada) {
            $this->portada = $portada;
        }
    }

    public function eliminarArchivo(string $nombreArchivo): void {
        if(!$nombreArchivo) return;

        $rutaArchivo = CARPETA_IMAGENES . $nombreArchivo;
        if(file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
    }
}
