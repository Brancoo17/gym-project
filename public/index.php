<?php

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\LoginController;
use Controllers\PaginasController;
use Controllers\AdminController;
use Controllers\ClienteController;
use Controllers\EntrenadorController;
use Controllers\PlanController;
use Controllers\HorarioController;
use Controllers\APIController;
use Controllers\EjercicioController;
use Controllers\RutinaController;
use Controllers\UsuarioController;
use Controllers\PagoController;
use Controllers\ConfiguracionController;
use Controllers\CuentaController;

$router = new Router();


// Públicas
$router->get('/', [PaginasController::class, 'index']);
$router->post('/', [PaginasController::class, 'index']);

// Auth
$router->get('/login', [LoginController::class, 'login']);
$router->post('/login', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);
$router->get('/olvide', [LoginController::class, 'olvide']);
$router->post('/olvide', [LoginController::class, 'olvide']);
$router->get('/recuperar', [LoginController::class, 'recuperar']);
$router->post('/recuperar', [LoginController::class, 'recuperar']);
$router->get('/crear-cuenta', [LoginController::class, 'crear']);
$router->post('/crear-cuenta', [LoginController::class, 'crear']);
$router->get('/confirmar-cuenta', [LoginController::class, 'confirmar']);
$router->get('/mensaje', [LoginController::class, 'mensaje']);

// Paneles por rol
$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/reservas', [AdminController::class, 'reservas']);
$router->get('/admin/reportes', [AdminController::class, 'reportes']);
$router->get('/admin/pagos', [AdminController::class, 'pagos']);
// Gestión de Clientes / Alumnos (admin)
$router->get('/admin/usuarios', [UsuarioController::class, 'index']);
$router->get('/admin/clientes', [UsuarioController::class, 'index']);
$router->get('/admin/usuarios/crear', [UsuarioController::class, 'crear']);
$router->post('/admin/usuarios/crear', [UsuarioController::class, 'crear']);
$router->get('/admin/usuarios/actualizar', [UsuarioController::class, 'actualizar']);
$router->post('/admin/usuarios/actualizar', [UsuarioController::class, 'actualizar']);
$router->get('/admin/usuarios/detalle', [UsuarioController::class, 'detalle']);
$router->post('/admin/usuarios/eliminar', [UsuarioController::class, 'eliminar']);
$router->post('/admin/usuarios/toggle-confirmar', [UsuarioController::class, 'toggleConfirmar']);
$router->get('/admin/usuarios/membresia-manual', [UsuarioController::class, 'membresiaManual']);
$router->post('/admin/usuarios/membresia-manual', [UsuarioController::class, 'membresiaManual']);
$router->get('/cliente', [ClienteController::class, 'index']);
$router->get('/cliente/reservar', [ClienteController::class, 'reservar']);
$router->get('/cliente/turnos', [ClienteController::class, 'turnos']);
$router->get('/cliente/rutinas', [ClienteController::class, 'rutina']);
$router->get('/cliente/ejercicios', [EjercicioController::class, 'index']);
$router->get('/cliente/planes', [ClienteController::class, 'planes']);
$router->post('/cliente/pago/crear-preferencia', [PagoController::class, 'crearPreferencia']);
$router->get('/cliente/pago/respuesta', [PagoController::class, 'respuesta']);
$router->get('/entrenador', [EntrenadorController::class, 'index']);
$router->get('/entrenador/asistencia', [EntrenadorController::class, 'asistencia']);
$router->get('/entrenador/alumnos', [EntrenadorController::class, 'alumnos']);
$router->get('/entrenador/alumnos/detalle', [EntrenadorController::class, 'detalleAlumno']);

// API Turnos, Reservas y Asistencias
$router->get('/api/horarios', [APIController::class, 'horarios']);
$router->get('/api/entrenador/horarios', [APIController::class, 'horariosEntrenador']);
$router->post('/api/reservas', [APIController::class, 'reservar']);
$router->post('/api/reservas/cancelar', [APIController::class, 'cancelar']);
$router->post('/api/asistencias/marcar', [APIController::class, 'marcarAsistencia']);
$router->post('/api/webhooks/mercadopago', [PagoController::class, 'webhook']);

// CRUD Planes (admin)
$router->get('/admin/planes', [PlanController::class, 'index']);
$router->get('/admin/planes/crear', [PlanController::class, 'crear']);
$router->post('/admin/planes/crear', [PlanController::class, 'crear']);
$router->get('/admin/planes/actualizar', [PlanController::class, 'actualizar']);
$router->post('/admin/planes/actualizar', [PlanController::class, 'actualizar']);
$router->post('/admin/planes/eliminar', [PlanController::class, 'eliminar']);

// CRUD Horarios (admin)
$router->get('/admin/horarios', [HorarioController::class, 'index']);
$router->get('/admin/horarios/crear', [HorarioController::class, 'crear']);
$router->post('/admin/horarios/crear', [HorarioController::class, 'crear']);
$router->get('/admin/horarios/actualizar', [HorarioController::class, 'actualizar']);
$router->post('/admin/horarios/actualizar', [HorarioController::class, 'actualizar']);
$router->post('/admin/horarios/eliminar', [HorarioController::class, 'eliminar']);

// CRUD Entrenadores (admin)
$router->get('/admin/entrenadores', [EntrenadorController::class, 'listar']);
$router->get('/admin/entrenadores/crear', [EntrenadorController::class, 'crear']);
$router->post('/admin/entrenadores/crear', [EntrenadorController::class, 'crear']);
$router->get('/admin/entrenadores/actualizar', [EntrenadorController::class, 'actualizar']);
$router->post('/admin/entrenadores/actualizar', [EntrenadorController::class, 'actualizar']);
$router->post('/admin/entrenadores/eliminar', [EntrenadorController::class, 'eliminar']);

// Banco de Ejercicios (admin y entrenador)
$router->get('/admin/ejercicios', [EjercicioController::class, 'index']);
$router->get('/admin/ejercicios/crear', [EjercicioController::class, 'crear']);
$router->post('/admin/ejercicios/crear', [EjercicioController::class, 'crear']);
$router->get('/admin/ejercicios/actualizar', [EjercicioController::class, 'actualizar']);
$router->post('/admin/ejercicios/actualizar', [EjercicioController::class, 'actualizar']);
$router->post('/admin/ejercicios/eliminar', [EjercicioController::class, 'eliminar']);

// Rutinas y WODs (admin y entrenador)
$router->get('/admin/rutinas', [RutinaController::class, 'index']);
$router->get('/admin/rutinas/crear', [RutinaController::class, 'crear']);
$router->post('/admin/rutinas/crear', [RutinaController::class, 'crear']);
$router->get('/admin/rutinas/actualizar', [RutinaController::class, 'actualizar']);
$router->post('/admin/rutinas/actualizar', [RutinaController::class, 'actualizar']);
$router->get('/admin/rutinas/detalle', [RutinaController::class, 'detalle']);
$router->post('/admin/rutinas/eliminar', [RutinaController::class, 'eliminar']);

// Configuración del Sitio (admin)
$router->get('/admin/configuracion', [ConfiguracionController::class, 'index']);
$router->post('/admin/configuracion', [ConfiguracionController::class, 'guardar']);

// Mi Cuenta / Perfil (todos los roles)
$router->get('/cuenta', [CuentaController::class, 'index']);
$router->post('/cuenta', [CuentaController::class, 'index']);

$router->comprobarRutas();


