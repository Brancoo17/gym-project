<?php
    $contenido = $contenido ?? "";
    $tipo = $tipo ?? 'landing';
    $nombre = $nombre ?? ($_SESSION['nombre'] ?? '');
    $rol = $_SESSION['rol'] ?? '';
    $login = $_SESSION['login'] ?? false;
    $configuracion = obtenerConfiguracion();
    $tieneLogo = (!empty($configuracion->logo) && file_exists(CARPETA_IMAGENES . $configuracion->logo));
    $tieneTurnos = !empty($configuracion->habilitar_turnos);
    $tieneCrossfit = !empty($configuracion->habilitar_crossfit);
    $textoRutinas = $tieneCrossfit ? 'Rutinas y WODs' : 'Rutinas';
    $textoRutinasCliente = $tieneCrossfit ? 'Mi Rutina y WOD' : 'Mis Rutinas';
    $urlActual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $esActivo = function(string $ruta) use ($urlActual): string {
        if($ruta === '/admin' || $ruta === '/entrenador' || $ruta === '/cliente') {
            return ($urlActual === $ruta) ? 'activo' : '';
        }
        return str_starts_with($urlActual, $ruta) ? 'activo' : '';
    };
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if($tieneLogo): ?>
        <link rel="icon" href="/imagenes/<?php echo s($configuracion->logo); ?>" type="image/webp">
    <?php else: ?>
        <link rel="icon" href="/build/img/gym-icon.png" type="image/png">
    <?php endif; ?>
    <title><?php echo s($configuracion->nombre); ?> | Gestión de gimnasio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/build/css/app.css">
</head>
<body class="tipo-<?php echo s($tipo); ?>">

<?php if($tipo === 'auth'): ?>
    <div class="auth-layout">
        <div class="auth-header-top">
            <a href="/" class="auth-btn-volver" title="Volver a la página de inicio">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Volver al inicio</span>
            </a>
            <a href="/" class="auth-logo" title="<?php echo s($configuracion->nombre); ?>">
                <?php if($tieneLogo): ?>
                    <img src="/imagenes/<?php echo s($configuracion->logo); ?>" alt="<?php echo s($configuracion->nombre); ?>" class="auth-logo-img">
                <?php else: ?>
                    <span><?php echo s($configuracion->nombre); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="auth-contenedor">
            <div class="auth-card">
                <?php echo $contenido; ?>
            </div>
        </div>
    </div>
<?php elseif($tipo === 'dashboard'): ?>
    <div class="dashboard" id="dashboard">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar__header">
                <div class="sidebar__logo-contenedor">
                    <a href="/" class="sidebar__logo" title="<?php echo s($configuracion->nombre); ?>">
                        <?php if($tieneLogo): ?>
                            <img src="/imagenes/<?php echo s($configuracion->logo); ?>" alt="<?php echo s($configuracion->nombre); ?>" class="sidebar__logo-img">
                            <i class="fa-solid fa-dumbbell sidebar__logo-icono-mini" title="<?php echo s($configuracion->nombre); ?>"></i>
                        <?php else: ?>
                            <span class="sidebar__logo-texto"><?php echo s($configuracion->nombre); ?></span>
                            <i class="fa-solid fa-dumbbell sidebar__logo-icono-mini" title="<?php echo s($configuracion->nombre); ?>"></i>
                        <?php endif; ?>
                    </a>
                </div>

                <button type="button" class="sidebar__toggle" id="sidebar_toggle" aria-label="Alternar menú" title="Desplegar / Colapsar navegación">
                    <i class="fa-solid fa-bars" id="sidebar_toggle_icono"></i>
                </button>
            </div>

            <div class="sidebar__colapsable" id="sidebar_colapsable">
                <a href="/cuenta" class="sidebar__usuario <?php echo $esActivo('/cuenta'); ?>" title="Mi Cuenta / Editar Perfil">
                    <i class="fa-solid fa-circle-user sidebar__usuario-icono"></i>
                    <div class="sidebar__usuario-info">
                        <p class="sidebar__nombre"><?php echo s($nombre); ?></p>
                        <span class="sidebar__rol"><?php echo s(ucfirst($rol)); ?></span>
                    </div>
                    <i class="fa-solid fa-gear sidebar__usuario-gear" title="Editar cuenta"></i>
                </a>

                <nav class="sidebar__nav">
                    <?php if($rol === 'admin'): ?>
                        <a href="/admin" class="<?php echo $esActivo('/admin'); ?>" title="Inicio">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Inicio</span>
                        </a>
                        <?php if($tieneTurnos): ?>
                            <a href="/admin/reservas" class="<?php echo $esActivo('/admin/reservas'); ?>" title="Agenda y Asistencia">
                                <i class="fa-solid fa-clipboard-user"></i>
                                <span>Agenda y Asistencia</span>
                            </a>
                            <a href="/admin/reportes" class="<?php echo $esActivo('/admin/reportes'); ?>" title="Métricas y Reportes">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Métricas y Reportes</span>
                            </a>
                            <a href="/admin/horarios" class="<?php echo $esActivo('/admin/horarios'); ?>" title="Horarios">
                                <i class="fa-solid fa-clock"></i>
                                <span>Horarios</span>
                            </a>
                        <?php else: ?>
                            <a href="/admin/reportes" class="<?php echo $esActivo('/admin/reportes'); ?>" title="Métricas y Reportes">
                                <i class="fa-solid fa-chart-line"></i>
                                <span>Métricas y Reportes</span>
                            </a>
                        <?php endif; ?>
                        <a href="/admin/planes" class="<?php echo $esActivo('/admin/planes'); ?>" title="Planes">
                            <i class="fa-solid fa-dumbbell"></i>
                            <span>Planes</span>
                        </a>
                        <a href="/admin/entrenadores" class="<?php echo $esActivo('/admin/entrenadores'); ?>" title="Entrenadores">
                            <i class="fa-solid fa-user-group"></i>
                            <span>Entrenadores</span>
                        </a>
                        <a href="/admin/rutinas" class="<?php echo $esActivo('/admin/rutinas'); ?>" title="<?php echo $textoRutinas; ?>">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span><?php echo $textoRutinas; ?></span>
                        </a>
                        <a href="/admin/ejercicios" class="<?php echo $esActivo('/admin/ejercicios'); ?>" title="Banco de Ejercicios">
                            <i class="fa-solid fa-heart-pulse"></i>
                            <span>Banco de Ejercicios</span>
                        </a>
                        <a href="/admin/usuarios" class="<?php echo $esActivo('/admin/usuarios'); ?>" title="Gestión de Clientes">
                            <i class="fa-solid fa-address-book"></i>
                            <span>Gestión de Clientes</span>
                        </a>
                        <a href="/admin/pagos" class="<?php echo $esActivo('/admin/pagos'); ?>" title="Pagos y Facturación">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>Pagos y Facturación</span>
                        </a>
                        <a href="/admin/configuracion" class="<?php echo $esActivo('/admin/configuracion'); ?>" title="Configuración">
                            <i class="fa-solid fa-gears"></i>
                            <span>Configuración</span>
                        </a>
                    <?php elseif($rol === 'entrenador'): ?>
                        <a href="/entrenador" class="<?php echo $esActivo('/entrenador'); ?>" title="Inicio">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Inicio</span>
                        </a>
                        <?php if($tieneTurnos): ?>
                            <a href="/entrenador/asistencia" class="<?php echo $esActivo('/entrenador/asistencia'); ?>" title="Clases y Control Asistencia">
                                <i class="fa-solid fa-clipboard-user"></i>
                                <span>Clases y Control Asistencia</span>
                            </a>
                        <?php endif; ?>
                        <a href="/entrenador/alumnos" class="<?php echo $esActivo('/entrenador/alumnos'); ?>" title="Alumnos">
                            <i class="fa-solid fa-users"></i>
                            <span>Alumnos</span>
                        </a>
                        <a href="/admin/rutinas" class="<?php echo $esActivo('/admin/rutinas'); ?>" title="<?php echo $textoRutinas; ?>">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span><?php echo $textoRutinas; ?></span>
                        </a>
                        <a href="/admin/ejercicios" class="<?php echo $esActivo('/admin/ejercicios'); ?>" title="Banco de Ejercicios">
                            <i class="fa-solid fa-heart-pulse"></i>
                            <span>Banco de Ejercicios</span>
                        </a>
                    <?php else: ?>
                        <a href="/cliente" class="<?php echo $esActivo('/cliente'); ?>" title="Inicio">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Inicio</span>
                        </a>

                        <?php if($tieneTurnos): ?>
                            <a href="/cliente/reservar" class="<?php echo $esActivo('/cliente/reservar'); ?>" title="Reservar Turno">
                                <i class="fa-solid fa-calendar-plus"></i>
                                <span>Reservar Turno</span>
                            </a>
                            <a href="/cliente/turnos" class="<?php echo $esActivo('/cliente/turnos'); ?>" title="Mis Turnos">
                                <i class="fa-solid fa-clipboard-list"></i>
                                <span>Mis Turnos</span>
                            </a>
                        <?php endif; ?>
                        <a href="/cliente/rutinas" class="<?php echo $esActivo('/cliente/rutinas'); ?>" title="<?php echo $textoRutinasCliente; ?>">
                            <i class="fa-solid fa-dumbbell"></i>
                            <span><?php echo $textoRutinasCliente; ?></span>
                        </a>
                        <a href="/cliente/ejercicios" class="<?php echo $esActivo('/cliente/ejercicios'); ?>" title="Banco de Ejercicios">
                            <i class="fa-solid fa-heart-pulse"></i>
                            <span>Banco de Ejercicios</span>
                        </a>
                        <a href="/cliente/planes" class="<?php echo $esActivo('/cliente/planes'); ?>" title="Mis Planes / Contratar">
                            <i class="fa-solid fa-tags"></i>
                            <span>Planes</span>
                        </a>
                    <?php endif; ?>
                    <a href="/logout" class="sidebar__nav-logout" title="Cerrar sesión">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Cerrar sesión</span>
                    </a>
                </nav>
            </div>
        </aside>
        <main class="dashboard__contenido">
            <?php echo $contenido; ?>
        </main>
    </div>
<?php else: ?>
    <header class="header">
        <div class="header__contenedor">
            <a href="/" class="header__logo">
                <?php if($tieneLogo): ?>
                    <img src="/imagenes/<?php echo s($configuracion->logo); ?>" alt="<?php echo s($configuracion->nombre); ?>" class="header__logo-img">
                <?php else: ?>
                    <?php echo s($configuracion->nombre); ?>
                <?php endif; ?>
            </a>
            <nav class="navegacion">
                <a href="/#planes">Planes</a>
                <?php if($tieneTurnos): ?>
                    <a href="/#horarios">Horarios</a>
                <?php endif; ?>
                <a href="/#ubicacion">Ubicación</a>
                <a href="/#contacto">Contacto</a>
                <?php if($login): ?>
                    <?php 
                        $urlPanel = match($rol) {
                            'admin' => '/admin',
                            'entrenador' => '/entrenador',
                            default => '/cliente'
                        };
                    ?>
                    <a href="<?php echo $urlPanel; ?>" class="navegacion__enlace-panel">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Panel</span>
                    </a>
                    <a href="/logout" class="navegacion__enlace-salir">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Salir</span>
                    </a>
                <?php else: ?>
                    <a href="/login">Iniciar sesión</a>
                    <a class="navegacion__cta" href="/crear-cuenta">Crear cuenta</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>
        <?php echo $contenido; ?>
    </main>
    <footer class="footer">
        <?php if(!empty($configuracion->instagram) || !empty($configuracion->facebook) || !empty($configuracion->tiktok)): ?>
            <div class="redes-sociales-barra">
                <?php if(!empty($configuracion->instagram)): ?>
                    <a href="<?php echo str_starts_with($configuracion->instagram, 'http') ? s($configuracion->instagram) : 'https://instagram.com/' . ltrim(s($configuracion->instagram), '@'); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn-red-social btn-red-social--instagram" 
                       title="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                <?php endif; ?>

                <?php if(!empty($configuracion->facebook)): ?>
                    <a href="<?php echo str_starts_with($configuracion->facebook, 'http') ? s($configuracion->facebook) : 'https://facebook.com/' . s($configuracion->facebook); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn-red-social btn-red-social--facebook" 
                       title="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                <?php endif; ?>

                <?php if(!empty($configuracion->tiktok)): ?>
                    <a href="<?php echo str_starts_with($configuracion->tiktok, 'http') ? s($configuracion->tiktok) : 'https://tiktok.com/@' . ltrim(s($configuracion->tiktok), '@'); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn-red-social btn-red-social--tiktok" 
                       title="TikTok">
                        <i class="fa-brands fa-tiktok"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <p><?php echo s($configuracion->nombre); ?> &copy; <?php echo date('Y'); ?></p>
    </footer>
<?php endif; ?>

    <script src="/build/js/app.js?v=<?php echo file_exists(__DIR__ . '/../public/build/js/app.js') ? filemtime(__DIR__ . '/../public/build/js/app.js') : '2.1'; ?>"></script>
    <?php echo $script ?? ''; ?>
</body>
</html>
