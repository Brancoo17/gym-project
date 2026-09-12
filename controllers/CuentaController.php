<?php

namespace Controllers;

use MVC\Router;
use Model\Usuario;

class CuentaController {
    public static function index(Router $router): void {
        isAuth();

        $usuarioId = $_SESSION['id'] ?? null;
        if(!$usuarioId) {
            header('Location: /login');
            exit;
        }

        $usuario = Usuario::find($usuarioId);
        if(!$usuario) {
            header('Location: /logout');
            exit;
        }

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'datos';

            if($action === 'datos') {
                $nombre = trim($_POST['nombre'] ?? '');
                $apellido = trim($_POST['apellido'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $email = trim($_POST['email'] ?? '');

                $dni = trim($_POST['dni'] ?? '');

                if(!$nombre) {
                    Usuario::setAlerta('error', 'El nombre es obligatorio');
                }
                if(!$apellido) {
                    Usuario::setAlerta('error', 'El apellido es obligatorio');
                }
                if($usuario->rol === 'cliente') {
                    if(!$dni) {
                        Usuario::setAlerta('error', 'El DNI es obligatorio');
                    } else {
                        $dniLimpio = preg_replace('/[\.\s-]/', '', $dni);
                        if(!preg_match('/^[0-9]{7,10}$/', $dniLimpio)) {
                            Usuario::setAlerta('error', 'El DNI debe contener entre 7 y 10 dígitos numéricos');
                        } else {
                            $usuario->dni = $dniLimpio;
                            $usuario->existeDni($usuario->id);
                        }
                    }
                }
                if(!$telefono) {
                    Usuario::setAlerta('error', 'El teléfono es obligatorio');
                }
                if(!$email) {
                    Usuario::setAlerta('error', 'El correo electrónico es obligatorio');
                } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Usuario::setAlerta('error', 'El formato del correo electrónico no es válido');
                } else {
                    // Validar si el email ya pertenece a otro usuario
                    $emailSanitizado = s($email);
                    $query = "SELECT id FROM usuarios WHERE email = '{$emailSanitizado}' AND id != {$usuario->id} LIMIT 1";
                    $db = Usuario::getDB();
                    $existeOtro = $db->query($query);
                    if($existeOtro && $existeOtro->num_rows > 0) {
                        Usuario::setAlerta('error', 'El correo electrónico ya está en uso por otra cuenta');
                    }
                }

                $alertas = Usuario::getAlertas();

                if(empty($alertas)) {
                    $usuario->nombre = $nombre;
                    $usuario->apellido = $apellido;
                    if($usuario->rol === 'cliente' && !empty($dniLimpio)) {
                        $usuario->dni = $dniLimpio;
                    }
                    $usuario->telefono = $telefono;
                    $usuario->email = $email;

                    $resultado = $usuario->guardar();
                    if($resultado) {
                        // Actualizar información en sesión
                        $_SESSION['nombre'] = $usuario->nombre . ' ' . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;

                        Usuario::setAlerta('exito', 'Tus datos personales fueron actualizados con éxito');
                    } else {
                        Usuario::setAlerta('error', 'Ocurrió un error al guardar los cambios');
                    }
                }
            } elseif($action === 'password') {
                $passwordActual = $_POST['password_actual'] ?? '';
                $passwordNuevo = $_POST['password_nuevo'] ?? '';
                $passwordConfirmar = $_POST['password_confirmar'] ?? '';

                if(!$passwordActual) {
                    Usuario::setAlerta('error', 'Debés ingresar tu contraseña actual');
                }
                if(!$passwordNuevo) {
                    Usuario::setAlerta('error', 'Debés ingresar una nueva contraseña');
                } elseif(strlen($passwordNuevo) < 6) {
                    Usuario::setAlerta('error', 'La nueva contraseña debe tener al menos 6 caracteres');
                }
                if($passwordNuevo !== $passwordConfirmar) {
                    Usuario::setAlerta('error', 'Las nuevas contraseñas no coinciden');
                }

                if(!empty($passwordActual)) {
                    // Verificar contraseña actual
                    $valido = password_verify($passwordActual, $usuario->password);
                    if(!$valido) {
                        Usuario::setAlerta('error', 'La contraseña actual no es correcta');
                    }
                }

                $alertas = Usuario::getAlertas();

                if(empty($alertas)) {
                    $usuario->password = $passwordNuevo;
                    $usuario->hashPassword();
                    $resultado = $usuario->guardar();

                    if($resultado) {
                        Usuario::setAlerta('exito', 'Tu contraseña fue modificada exitosamente');
                    } else {
                        Usuario::setAlerta('error', 'Ocurrió un error al actualizar la contraseña');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('cuenta/index', [
            'usuario' => $usuario,
            'alertas' => $alertas,
            'tipo' => 'dashboard'
        ]);
    }
}
