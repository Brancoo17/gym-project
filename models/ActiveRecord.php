<?php
namespace Model;
class ActiveRecord {

    protected static \mysqli $db;
    protected static string $tabla = '';
    protected static array $columnasDB = [];

    protected static array $alertas = [];

    public ?int $id = null;

    public static function setDB(\mysqli $database) {
        self::$db = $database;
    }

    public static function getDB(): \mysqli {
        return self::$db;
    }

    /**
     * Ejecuta una consulta SQL y retorna los registros como arrays asociativos (ideal para reportes y agregaciones)
     */
    public static function queryArray(string $query): array {
        $resultado = self::$db->query($query);

        if(!($resultado instanceof \mysqli_result)) {
            return [];
        }

        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = $registro;
        }

        $resultado->free();

        return $array;
    }

    public static function setAlerta(string $tipo, string $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    public static function consultarSQL(string $query): array {
        $resultado = self::$db->query($query);

        if(!($resultado instanceof \mysqli_result)) {
            return [];
        }

        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        $resultado->free();

        return $array;
    }

    protected static function crearObjeto(array $registro) {
        $objeto = new static;

        foreach($registro as $key => $value ) {
            if(property_exists( $objeto, $key  )) {
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }

    public function atributos() {
        $atributos = [];
        foreach(static::$columnasDB as $columna) {
            if($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public function sanitizarAtributos() {
        $atributos = $this->atributos();
        $sanitizado = [];
        foreach($atributos as $key => $value ) {
            if(is_null($value)) {
                $sanitizado[$key] = null;
            } else {
                $sanitizado[$key] = self::$db->escape_string((string)$value);
            }
        }
        return $sanitizado;
    }

    public function sincronizar($args=[]) {
        foreach($args as $key => $value) {
          if(property_exists($this, $key) && !is_null($value)) {
            $this->$key = $value;
          }
        }
    }

    public function guardar() {
        $resultado = '';
        if(!is_null($this->id)) {
            $resultado = $this->actualizar();
        } else {
            $resultado = $this->crear();
        }
        return $resultado;
    }

    public static function all() {
        $query = "SELECT * FROM " . static::$tabla;
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    public static function find(int $id) {
        $query = "SELECT * FROM " . static::$tabla  ." WHERE id = {$id}";
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    public static function get(int $limite) {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT {$limite}";
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    public static function where(string $columna, mixed $valor) {
        $query = "SELECT * FROM " . static::$tabla  ." WHERE {$columna} = '{$valor}'";
        $resultado = self::consultarSQL($query);
        return array_shift( $resultado ) ;
    }

    public static function whereAll(string $columna, mixed $valor) {
        $query = "SELECT * FROM " . static::$tabla  ." WHERE {$columna} = '{$valor}'";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }


    public static function SQL(string $query) {
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    public function crear() {
        $atributos = $this->sanitizarAtributos();

        $columnas = join(', ', array_keys($atributos));
        $valores = array_map(function($valor) {
            return is_null($valor) ? "NULL" : "'{$valor}'";
        }, array_values($atributos));
        $valoresStr = join(', ', $valores);

        $query = " INSERT INTO " . static::$tabla . " ( {$columnas} ) VALUES ( {$valoresStr} ) ";

        $resultado = self::$db->query($query);
        if($resultado) {
            $this->id = self::$db->insert_id;
        }
        return [
           'resultado' =>  $resultado,
           'id' => self::$db->insert_id
        ];
    }

    public function actualizar() {
        $atributos = $this->sanitizarAtributos();

        $valores = [];
        foreach($atributos as $key => $value) {
            if(is_null($value)) {
                $valores[] = "{$key}=NULL";
            } else {
                $valores[] = "{$key}='{$value}'";
            }
        }

        $query = "UPDATE " . static::$tabla ." SET ";
        $query .=  join(', ', $valores );
        $query .= " WHERE id = '" . self::$db->escape_string((string)$this->id) . "' ";
        $query .= " LIMIT 1 ";

        $resultado = self::$db->query($query);
        return $resultado;
    }

    public function eliminar() {
        $query = "DELETE FROM "  . static::$tabla . " WHERE id = " . self::$db->escape_string((string)$this->id) . " LIMIT 1";
        $resultado = self::$db->query($query);
        return $resultado;
    }

}
