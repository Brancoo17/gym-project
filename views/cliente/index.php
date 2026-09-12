<?php 
/** @var string $nombre */ 
/** @var array $proximosTurnos */
/** @var array $membresiasActivas */
$proximosTurnos = $proximosTurnos ?? [];
$membresiasActivas = $membresiasActivas ?? [];
?>

<h1>Mi cuenta</h1>
<p>Hola <?php echo s($nombre); ?>. Gestioná tus turnos, clases y entrenamientos desde tu panel.</p>

<?php if(!empty($membresiasActivas)): ?>
    <div class="membresias-activas-badges">
        <p>Membresías activas: </p>
        <?php foreach($membresiasActivas as $mem): 
            $dias = isset($mem['dias_restantes']) ? (int)$mem['dias_restantes'] : null;
            $fechaFinFormateada = !empty($mem['fecha_fin']) ? date('d/m/Y', strtotime($mem['fecha_fin'])) : '';
        ?>
            <span class="badge badge--verde membresia-badge" title="Válida hasta <?php echo $fechaFinFormateada; ?>">
                <i class="fa-solid fa-circle-check"></i> <?php echo s($mem['nombre']); ?>
                <?php if(!empty($fechaFinFormateada)): ?>
                    <small style="opacity: 0.9; font-size: 0.85em; margin-left: 0.3rem;">(hasta <?php echo $fechaFinFormateada; ?>)</small>
                <?php endif; ?>
            </span>
            <?php if($dias !== null && $dias >= 0 && $dias <= 7): ?>
                <span class="badge <?php echo $dias === 0 ? 'badge--rojo' : 'badge--amarillo'; ?>">
                    <i class="fa-solid fa-clock"></i> <?php echo $dias === 0 ? 'Vence hoy' : ($dias === 1 ? 'Vence mañana' : "Vence en {$dias} días"); ?>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Si alguna membresía vence en 7 días o menos, mostrar banner preventivo -->
    <?php 
    $membresiasPorVencer = array_filter($membresiasActivas, function($m) {
        $d = isset($m['dias_restantes']) ? (int)$m['dias_restantes'] : 99;
        return $d >= 0 && $d <= 7;
    });
    ?>
    <?php if(!empty($membresiasPorVencer)): ?>
        <div class="banner-alerta-sin-membresia" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fcd34d; margin-bottom: 2.5rem;">
            <div class="banner-alerta-sin-membresia__icono" style="background: #fef3c7; color: #d97706; border-color: #fde68a;">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="banner-alerta-sin-membresia__cuerpo">
                <div class="banner-alerta-sin-membresia__header">
                    <h3 style="color: #92400e;">¡Aviso de vencimiento de tu membresía!</h3>
                    <span class="badge badge--amarillo"><i class="fa-solid fa-clock"></i> Próximo a vencer</span>
                </div>
                <p style="color: #78350f;">
                    <?php foreach($membresiasPorVencer as $mpv): 
                        $d = (int)$mpv['dias_restantes'];
                        $plazoTexto = $d === 0 ? 'vence hoy' : ($d === 1 ? 'vence mañana' : "vence en {$d} días (el " . date('d/m/Y', strtotime($mpv['fecha_fin'])) . ")");
                    ?>
                        Tu pase de <strong><?php echo s($mpv['nombre']); ?></strong> <?php echo $plazoTexto; ?>. 
                    <?php endforeach; ?>
                    Renovala a tiempo para continuar entrenando y no perder el acceso a la reserva de turnos ni a tus rutinas diarias.
                </p>
                <div class="banner-alerta-sin-membresia__acciones">
                    <a href="/cliente/planes" class="boton boton--primario">
                        <i class="fa-solid fa-cart-shopping"></i> Ver Planes y Renovar
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="membresias-activas-badges" style="margin-bottom: 2rem;">
        <p>Estado de membresía: </p>
        <span class="badge badge--rojo membresia-badge">
            <i class="fa-solid fa-circle-xmark"></i> Sin membresía activa
        </span>
    </div>

    <!-- Banner amigable explicando por qué no figuran turnos ni rutinas -->
    <div class="banner-alerta-sin-membresia">
        <div class="banner-alerta-sin-membresia__icono">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="banner-alerta-sin-membresia__cuerpo">
            <div class="banner-alerta-sin-membresia__header">
                <h3>No contás con una membresía activa en este momento</h3>
                <span class="badge badge--rojo"><i class="fa-solid fa-lock"></i> Plan Requerido</span>
            </div>
            <p>
                Actualmente no tenés ningún plan contratado o tu suscripción ha vencido. Por este motivo <strong>no podrás visualizar turnos disponibles para reservar</strong> en el calendario ni acceder a las <strong>rutinas y WODs diarios</strong> de entrenamiento.
            </p>
            <div class="banner-alerta-sin-membresia__acciones">
                <a href="/cliente/planes" class="boton boton--primario">
                    <i class="fa-solid fa-cart-shopping"></i> Ver Planes y Adquirir Membresía
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="cards-panel">
    <a class="card-panel" href="/cliente/reservar">
        <div class="card-panel__header">
            <h3>Reservar Turno</h3>
            <i class="fa-solid fa-calendar-plus icono--azul"></i>
        </div>
        <p>Elegí día y horario para entrenar según cupo disponible.</p>
    </a>

    <a class="card-panel" href="/cliente/turnos">
        <div class="card-panel__header">
            <h3>Mis Turnos</h3>
            <i class="fa-solid fa-clipboard-list icono--azul"></i>
        </div>
        <p>Consultá tus reservas activas y tu historial de clases.</p>
    </a>

    <a class="card-panel" href="/cliente/rutinas">
        <div class="card-panel__header">
            <h3>Mi Rutina y WOD</h3>
            <i class="fa-solid fa-dumbbell icono--azul"></i>
        </div>
        <p>Consultá tus ejercicios del día, cargas, técnica y WODs diarios.</p>
    </a>

    <a class="card-panel" href="/cliente/ejercicios">
        <div class="card-panel__header">
            <h3>Banco de Ejercicios</h3>
            <i class="fa-solid fa-heart-pulse icono--verde"></i>
        </div>
        <p>Consultá la técnica correcta, grupos musculares y videos de cada ejercicio.</p>
    </a>

    <a class="card-panel" href="/cliente/planes">
        <div class="card-panel__header">
            <h3>Planes</h3>
            <i class="fa-solid fa-cart-shopping icono--naranja"></i>
        </div>
        <p>Conocé y adquirí nuestras distintas opciones de entrenamiento y membresías.</p>
    </a>
</div>

<!-- Próximos turnos reservados -->
<div class="proximos-turnos-seccion">
    <div class="proximos-turnos-seccion__header">
        <h2 class="proximos-turnos-seccion__titulo">Tus Próximos Turnos</h2>
        <a href="/cliente/reservar" class="boton proximos-turnos-seccion__btn-reservar">+ Reservar Turno</a>
    </div>

    <?php if(empty($proximosTurnos)): ?>
        <div class="proximos-turnos-seccion__vacio">
            <i class="fa-regular fa-calendar-check"></i>
            <h4>No tenés turnos reservados próximos</h4>
            <?php if(empty($membresiasActivas)): ?>
                <p>Para poder reservar turnos en las distintas clases, primero debés contar con una membresía activa.</p>
                <a href="/cliente/planes" class="boton"><i class="fa-solid fa-cart-shopping"></i> Ver Planes Disponibles</a>
            <?php else: ?>
                <p>Elegí un día y asegurá tu lugar para entrenar.</p>
                <a href="/cliente/reservar" class="boton">+ Reservar Turno</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="proximos-turnos-grid">
            <?php foreach($proximosTurnos as $turno): 
                $timestamp = strtotime($turno->fecha);
                $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $diaNombre = $diasSemana[(int)date('w', $timestamp)];
                $fechaFormato = date('d/m/Y', $timestamp);
            ?>
                <div class="card-proximo-turno">
                    <div class="card-proximo-turno__header">
                        <span class="card-proximo-turno__hora">
                            <i class="fa-regular fa-clock"></i> <?php echo s(substr($turno->hora_inicio, 0, 5)); ?> - <?php echo s(substr($turno->hora_fin, 0, 5)); ?> hs
                        </span>
                        <span class="card-proximo-turno__fecha">
                            <?php echo $diaNombre; ?> <?php echo $fechaFormato; ?>
                        </span>
                    </div>

                    <h4 class="card-proximo-turno__titulo"><?php echo s($turno->plan_nombre); ?></h4>
                    <?php if(!empty($turno->descripcion)): ?>
                        <div class="card-proximo-turno__descripcion">
                            <i class="fa-solid fa-circle-info"></i>
                            <span><?php echo s($turno->descripcion); ?></span>
                        </div>
                    <?php endif; ?>
                    <p class="card-proximo-turno__profesor">
                        <i class="fa-solid fa-user-tie"></i> Prof. <strong><?php echo s($turno->entrenador); ?></strong>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
