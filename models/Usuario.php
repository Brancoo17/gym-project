<?php

namespace Model;

class Usuario extends ActiveRecord {
    protected static string $tabla = 'usuarios';
    protected static array $columnasDB = ['id', 'nombre', 'apellido', 'dni', 'email', 'password', 'telefono', 'rol', 'confirmado', 'token'];

    public string $nombre;
    public string $apellido;
    public ?string $dni = null;
    public string $email;
    public string $password;
    public string $telefono;
    public string $rol;
    public mixed $confirmado;
    public string $token;
    public mixed $total_clases = 0;
    public ?string $clases_nombres = '';

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->dni = $args['dni'] ?? null;
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->rol = $args['rol'] ?? 'cliente';
        $this->confirmado = $args['confirmado'] ?? 0;
        $this->token = $args['token'] ?? '';
        $this->total_clases = $args['total_clases'] ?? 0;
        $this->clases_nombres = $args['clases_nombres'] ?? '';
    }

    public function validarNuevaCuenta() {
        self::$alertas = [];
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
        if(!$this->dni) {
            self::$alertas['error'][] = 'El DNI es obligatorio';
        } elseif(!preg_match('/^[0-9]{7,10}$/', preg_replace('/[\.\s-]/', '', $this->dni))) {
            self::$alertas['error'][] = 'El DNI debe contener entre 7 y 10 dígitos numéricos';
        }
        if(!$this->telefono) {
            self::$alertas['error'][] = 'El teléfono es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El password debe tener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        return self::$alertas;
    }

    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        return self::$alertas;
    }

    public function validarPassword() {
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El password debe tener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    public function existeUsuario() {
        $emailSeguro = self::$db->escape_string($this->email ?? '');
        $query = "SELECT * FROM " . self::$tabla . " WHERE email = '{$emailSeguro}' LIMIT 1";
        $resultado = self::$db->query($query);

        if($resultado->num_rows) {
            self::$alertas['error'][] = 'El usuario ya está registrado';
        }

        return $resultado;
    }

    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken() {
        $this->token = bin2hex(random_bytes(32));
    }

    public function comprobarPasswordAndVerificado(string $password) {
        $resultado = password_verify($password, $this->password);

        if(!$resultado) {
            self::$alertas['error'][] = 'Password incorrecto';
            return false;
        }

        if(!(int)$this->confirmado) {
            self::$alertas['error'][] = 'Cuenta no confirmada';
            return false;
        }

        return true;
    }

    public function validarEntrenador(bool $esNuevo = true): array {
        self::$alertas = [];
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        } elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'El email no tiene un formato válido';
        }
        if(!$this->telefono) {
            self::$alertas['error'][] = 'El teléfono es obligatorio';
        }

        if($esNuevo) {
            if(!$this->password) {
                self::$alertas['error'][] = 'La contraseña inicial es obligatoria';
            } elseif(strlen($this->password) < 6) {
                self::$alertas['error'][] = 'La contraseña debe tener al menos 6 caracteres';
            }
        } else {
            if($this->password && strlen($this->password) < 6) {
                self::$alertas['error'][] = 'La nueva contraseña debe tener al menos 6 caracteres';
            }
        }

        return self::$alertas;
    }

    public function validarCliente(bool $esNuevo = true): array {
        self::$alertas = [];

        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->apellido) {
            self::$alertas['error'][] = 'El apellido es obligatorio';
        }
        if(!$this->dni) {
            self::$alertas['error'][] = 'El DNI es obligatorio';
        } elseif(!preg_match('/^[0-9]{7,10}$/', preg_replace('/[\.\s-]/', '', $this->dni))) {
            self::$alertas['error'][] = 'El DNI debe contener entre 7 y 10 dígitos numéricos';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        } elseif(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'El email no tiene un formato válido';
        }
        if(!$this->telefono) {
            self::$alertas['error'][] = 'El teléfono es obligatorio';
        }

        if($esNuevo) {
            if(!$this->password) {
                self::$alertas['error'][] = 'La contraseña inicial es obligatoria';
            } elseif(strlen($this->password) < 6) {
                self::$alertas['error'][] = 'La contraseña debe tener al menos 6 caracteres';
            }
        } else {
            if(!empty($this->password) && strlen($this->password) < 6) {
                self::$alertas['error'][] = 'La nueva contraseña debe tener al menos 6 caracteres';
            }
        }

        return self::$alertas;
    }

    public function existeUsuarioExcluyendoId(?int $id = null): bool {
        $emailSeguro = self::$db->escape_string($this->email);
        $query = "SELECT id FROM " . self::$tabla . " WHERE email = '{$emailSeguro}'";
        if($id) {
            $query .= " AND id != {$id}";
        }
        $query .= " LIMIT 1";
        $resultado = self::$db->query($query);

        if($resultado && $resultado->num_rows) {
            self::$alertas['error'][] = 'El email ya se encuentra registrado por otro usuario';
            return true;
        }

        return false;
    }

    public function existeDni(?int $id = null): bool {
        if(empty($this->dni)) return false;
        $dniLimpio = preg_replace('/[\.\s-]/', '', $this->dni);
        $dniSeguro = self::$db->escape_string($dniLimpio);
        $query = "SELECT id FROM " . self::$tabla . " WHERE REPLACE(REPLACE(REPLACE(dni, '.', ''), ' ', ''), '-', '') = '{$dniSeguro}' AND rol = 'cliente'";
        if($id) {
            $query .= " AND id != {$id}";
        }
        $query .= " LIMIT 1";
        $resultado = self::$db->query($query);

        if($resultado && $resultado->num_rows) {
            self::$alertas['error'][] = 'El DNI ya se encuentra registrado por otro cliente';
            return true;
        }

        return false;
    }
}
