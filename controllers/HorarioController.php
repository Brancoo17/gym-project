<?php

namespace Controllers;

use MVC\Router;
use Model\Horario;
use Model\Plan;
use Model\Usuario;

class HorarioController {

    public static function index(Router $router): void {
        isAdmin();

        $horarios = Horario::SQL("
            SELECT horarios.*, planes.nombre AS plan_nombre,
                   CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS entrenador
            FROM horarios
            INNER JOIN planes ON planes.id = horarios.plan_id
            INNER JOIN usuarios ON usuarios.id = horarios.entrenador_id
            ORDER BY horarios.dia_semana, horarios.hora_inicio
        ");

        $planes = Plan::all();
        $resultado = $_GET['resultado'] ?? null;
        $total = $_GET['total'] ?? null;

        $router->render('admin/horarios/index', [
            'horarios' => $horarios,
            'planes' => $planes,
            'resultado' => $resultado,
            'total' => $total,
            'nombre' => $_SESSION['nombre'] ?? '',
            'tipo' => 'dashboard'
        ]);
    }

    public static function crear(Router $router): void {
        isAdmin();

        $horario = new Horario();
        $alertas = [];
        $diasSeleccionados = [1, 3, 5]; // Default: Lunes, Miércoles, Viernes

        $planes = Plan::all();
        $entrenadores = Usuario::SQL("SELECT * FROM usuarios WHERE rol IN ('entrenador', 'admin') ORDER BY nombre");

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datosHorario = $_POST['horario'] ?? [];
            $diasSemana = $_POST['dias_semana'] ?? [];
            $generarBloques = isset($_POST['generar_bloques']) && $_POST['generar_bloques'] === '1';
            $duracionMinutos = filter_var($_POST['duracion_minutos'] ?? 60, FILTER_VALIDATE_INT);

            $diasSeleccionados = array_map('intval', $diasSemana);

            $cupo = (!isset($datosHorario['cupo']) || trim((string)$datosHorario['cupo']) === '') ? null : (int)$datosHorario['cupo'];
            $descripcion = trim($datosHorario['descripcion'] ?? '');
            $horario->sincronizar($datosHorario);
            $horario->cupo = $cupo;
            $horario->descripcion = $descripcion !== '' ? $descripcion : null;

            if(empty($diasSemana)) {
                $alertas['error'][] = 'Debes seleccionar al menos un día de la semana';
            }

            if(!$horario->plan_id) {
                $alertas['error'][] = 'El plan es obligatorio';
            }
            if(!$horario->entrenador_id) {
                $alertas['error'][] = 'El entrenador es obligatorio';
            }
            if(!$horario->hora_inicio) {
                $alertas['error'][] = 'La hora de inicio es obligatoria';
            }
            if(!$horario->hora_fin) {
                $alertas['error'][] = 'La hora de fin es obligatoria';
            }
            if($horario->hora_inicio && $horario->hora_fin && $horario->hora_inicio >= $horario->hora_fin) {
                $alertas['error'][] = 'La hora de fin debe ser posterior a la hora de inicio';
            }
            if($horario->cupo !== null && $horario->cupo !== '') {
                if(!is_numeric($horario->cupo) || (int)$horario->cupo <= 0) {
                    $alertas['error'][] = 'El cupo debe ser un número mayor a cero';
                }
            }

            // Calcular franjas
            $bloques = [];
            if(empty($alertas)) {
                $inicioTimestamp = strtotime($horario->hora_inicio);
                $finTimestamp = strtotime($horario->hora_fin);

                if($generarBloques) {
                    $duracionSegundos = ($duracionMinutos ?: 60) * 60;
                    $actual = $inicioTimestamp;

                    while($actual + $duracionSegundos <= $finTimestamp) {
                        $bloques[] = [
                            'hora_inicio' => date('H:i:s', $actual),
                            'hora_fin' => date('H:i:s', $actual + $duracionSegundos)
                        ];
                        $actual += $duracionSegundos;
                    }

                    if(empty($bloques)) {
                        $alertas['error'][] = 'La duración seleccionada supera el rango entre la hora de inicio y fin';
                    }
                } else {
                    $bloques[] = [
                        'hora_inicio' => date('H:i:s', $inicioTimestamp),
                        'hora_fin' => date('H:i:s', $finTimestamp)
                    ];
                }
            }

            if(empty($alertas)) {
                $creados = 0;
                foreach($diasSemana as $dia) {
                    foreach($bloques as $bloque) {
                        $nuevoHorario = new Horario([
                            'plan_id' => $horario->plan_id,
                            'entrenador_id' => $horario->entrenador_id,
                            'dia_semana' => (int)$dia,
                            'hora_inicio' => $bloque['hora_inicio'],
                            'hora_fin' => $bloque['hora_fin'],
                            'cupo' => $horario->cupo !== null ? (int)$horario->cupo : null,
                            'descripcion' => $horario->descripcion ?: null
                        ]);
                        $nuevoHorario->guardar();
                        $creados++;
                    }
                }

                header("Location: /admin/horarios?resultado=1&total={$creados}");
                return;
            }
        }

        $router->render('admin/horarios/crear', [
            'horario' => $horario,
            'planes' => $planes,
            'entrenadores' => $entrenadores,
            'diasSeleccionados' => $diasSeleccionados,
            'alertas' => $alertas,
            'nombre' => $_SESSION['nombre'] ?? '',
            'tipo' => 'dashboard'
        ]);
    }

    public static function actualizar(Router $router): void {
        isAdmin();

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

        if(!$id) {
            header('Location: /admin/horarios');
            return;
        }

        $horario = Horario::find($id);

        if(!$horario) {
            header('Location: /admin/horarios');
            return;
        }

        $alertas = [];
        $planes = Plan::all();
        $entrenadores = Usuario::SQL("SELECT * FROM usuarios WHERE rol IN ('entrenador', 'admin') ORDER BY nombre");

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datosHorario = $_POST['horario'] ?? [];
            $cupo = (!isset($datosHorario['cupo']) || trim((string)$datosHorario['cupo']) === '') ? null : (int)$datosHorario['cupo'];
            $descripcion = trim($datosHorario['descripcion'] ?? '');
            $horario->sincronizar($datosHorario);
            $horario->cupo = $cupo;
            $horario->descripcion = $descripcion !== '' ? $descripcion : null;

            $alertas = $horario->validarHorario();

            if(empty($alertas)) {
                $resultado = $horario->guardar();

                if($resultado) {
                    header('Location: /admin/horarios?resultado=2');
                    return;
                }
            }
        }

        $router->render('admin/horarios/actualizar', [
            'horario' => $horario,
            'planes' => $planes,
            'entrenadores' => $entrenadores,
            'alertas' => $alertas,
            'nombre' => $_SESSION['nombre'] ?? '',
            'tipo' => 'dashboard'
        ]);
    }

    public static function eliminar(): void {
        isAdmin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

            if($id) {
                $horario = Horario::find($id);
                if($horario) {
                    $horario->eliminar();
                    header('Location: /admin/horarios?resultado=3');
                    return;
                }
            }
        }

        header('Location: /admin/horarios');
    }
}
