<?php

namespace Controllers;

use MVC\Router;
use Model\Plan;
use Model\Horario;

class PaginasController {
    public static function index(Router $router) {
        $planes = Plan::SQL("SELECT * FROM planes WHERE activo = 1");
        $horarios = Horario::SQL("
            SELECT horarios.*, planes.nombre AS plan_nombre,
                   CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS entrenador
            FROM horarios
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios ON usuarios.id = horarios.entrenador_id
            ORDER BY horarios.dia_semana, horarios.hora_inicio
        ");

        $enviado = false;
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = s($_POST['nombre'] ?? '');
            $email = s($_POST['email'] ?? '');
            $mensaje = s($_POST['mensaje'] ?? '');
            if($nombre && $email && $mensaje) {
                $enviado = true;
            }
        }

        $router->render('paginas/index', [
            'planes' => $planes,
            'horarios' => $horarios,
            'enviado' => $enviado,
            'tipo' => 'landing'
        ]);
    }
}
