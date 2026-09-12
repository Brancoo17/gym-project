<?php

namespace Model;

class AvisoEnviado extends ActiveRecord {
    protected static string $tabla = 'avisos_enviados';
    protected static array $columnasDB = ['id', 'membresia_id', 'tipo', 'enviado_at'];

    public ?int $id;
    public mixed $membresia_id;
    public string $tipo;
    public string $enviado_at;

    public function __construct(array $args = []) {
        $this->id = $args['id'] ?? null;
        $this->membresia_id = $args['membresia_id'] ?? null;
        $this->tipo = $args['tipo'] ?? '';
        $this->enviado_at = $args['enviado_at'] ?? date('Y-m-d H:i:s');
    }

    public function validar(): array {
        self::$alertas = [];

        if(!$this->membresia_id || !is_numeric($this->membresia_id)) {
            self::$alertas['error'][] = 'El ID de la membresía es obligatorio';
        }
        if(!in_array($this->tipo, ['7dias', 'hoy'])) {
            self::$alertas['error'][] = 'El tipo de aviso debe ser 7dias o hoy';
        }

        return self::$alertas;
    }

    /**
     * Verifica si ya se envió un aviso de cierto tipo para una membresía
     */
    public static function yaEnviado(int $membresiaId, string $tipo): bool {
        $query = "SELECT id FROM " . static::$tabla . " WHERE membresia_id = {$membresiaId} AND tipo = '{$tipo}' LIMIT 1";
        $resultado = self::SQL($query);
        return !empty($resultado);
    }

    /**
     * Registra un nuevo aviso enviado
     */
    public static function registrar(int $membresiaId, string $tipo): bool {
        $aviso = new self([
            'membresia_id' => $membresiaId,
            'tipo' => $tipo,
            'enviado_at' => date('Y-m-d H:i:s')
        ]);
        $resultado = $aviso->guardar();
        return (bool)($resultado['resultado'] ?? false);
    }
}
