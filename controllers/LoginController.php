<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;
use Classes\Email;

class LoginController {
    public static function login(Router $router) {

        $alertas = [];

        if(isset($_GET['resultado']) && $_GET['resultado'] === '1') {
            Usuario::setAlerta('exito', 'Password reestablecido correctamente');
        }

        $auth = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email);

                if($usuario) {
                    $verificado = $usuario->comprobarPasswordAndVerificado($auth->password);

                    if($verificado) {
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['rol'] = $usuario->rol;
                        $_SESSION['login'] = true;

                        if($usuario->rol === 'admin') {
                            header('Location: /admin');
                            return;
                        }
                        if($usuario->rol === 'entrenador') {
                            header('Location: /entrenador');
                            return;
                        }
                        header('Location: /cliente');
                        return;
                    }
                } else {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                    $auth->email = '';
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'alertas' => $alertas,
            'auth' => $auth,
            'tipo' => 'auth'
        ]);
    }

    public static function logout() {
        $_SESSION = [];
        header('Location: /');
    }

    public static function olvide(Router $router) {

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email);

                if($usuario && (int)$usuario->confirmado === 1) {
                    $usuario->crearToken();
                    $usuario->guardar();

                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    Usuario::setAlerta('exito', 'Revisa tu email');
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas,
            'tipo' => 'auth'
        ]);
    }

    public static function recuperar(Router $router) {

        $alertas = [];
        $error = false;

        $token = s($_GET['token'] ?? '');

        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();

            if(($_POST['password2'] ?? '') !== ($_POST['password'] ?? '')) {
                Usuario::setAlerta('error', 'Los passwords no coinciden');
            }

            $alertas = Usuario::getAlertas();

            if(empty($alertas) && $usuario) {
                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = '';
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /login?resultado=1');
                    return;
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error,
            'tipo' => 'auth'
        ]);
    }

    public static function crear(Router $router) {

        $usuario = new Usuario;
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario->sincronizar($_POST);
            $usuario->rol = 'cliente';
            if(!empty($usuario->dni)) {
                $usuario->dni = preg_replace('/[\.\s-]/', '', $usuario->dni);
            }

            $alertas = $usuario->validarNuevaCuenta();

            $password2 = $_POST['password2'] ?? '';
            if($usuario->password !== $password2) {
                $alertas['error'][] = 'Los passwords no coinciden';
            }

            if(empty($alertas)) {
                $usuario->existeDni();
                $resultado = $usuario->existeUsuario();

                $alertas = Usuario::getAlertas();

                if(empty($alertas)) {
                    $usuario->hashPassword();
                    $usuario->crearToken();

                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    $resultado = $usuario->guardar();

                    if($resultado) {
                        header('Location: /mensaje');
                        return;
                    }
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas,
            'tipo' => 'auth'
        ]);
    }

    public static function confirmar(Router $router) {

        $alertas = [];
        $token = s($_GET['token'] ?? '');
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            $usuario->confirmado = 1;
            $usuario->token = '';
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas,
            'tipo' => 'auth'
        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje', [
            'tipo' => 'auth'
        ]);
    }
}
