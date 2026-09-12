<?php
/** @var \Model\Usuario $alumno */
/** @var array $stats */
/** @var array $membresias */
/** @var array $clases */
/** @var array $rutinas */

$nombreCompleto = $alumno->nombre . ' ' . $alumno->apellido;
$iniciales = strtoupper(substr($alumno->nombre, 0, 1) . substr($alumno->apellido, 0, 1));
$esConfirmado = ((int)$alumno->confirmado === 1);
$telLimpio = preg_replace('/[^0-9]/', '', $alumno->telefono ?? '');
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Ficha de Seguimiento del Alumno</h1>
        <p>Historial de clases, asistencias y rutinas de <?php echo s($nombreCompleto); ?>.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/entrenador/alumnos" class="boton-accion">
            <i class="fa-solid fa-arrow-left"></i> Volver a Alumnos
        </a>
    </div>
</div>

<!-- Perfil Principal del Alumno -->
<div class="ficha-alumno-perfil">
    <div class="ficha-alumno-perfil__principal">
        <div class="ficha-alumno-perfil__avatar">
            <?php echo $iniciales; ?>
        </div>
        <div class="ficha-alumno-perfil__info">
            <h2><?php echo s($nombreCompleto); ?></h2>
            <div class="datos-contacto">
                <?php if(!empty($alumno->dni)): ?>
                    <span class="contacto-item">
                        <i class="fa-solid fa-id-card"></i> DNI: <strong><?php echo s($alumno->dni); ?></strong>
                    </span>
                <?php endif; ?>

                <a href="mailto:<?php echo s($alumno->email); ?>">
                    <i class="fa-regular fa-envelope"></i> <?php echo s($alumno->email); ?>
                </a>

                <?php if(!empty($alumno->telefono)): ?>
                    <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($alumno->nombre); ?>%2C%20te%20escribo%20desde%20el%20gimnasio." 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="contacto-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i> <?php echo s($alumno->telefono); ?>
                    </a>
                <?php else: ?>
                    <span><i class="fa-solid fa-phone-slash"></i> Sin teléfono</span>
                <?php endif; ?>

                <?php if($esConfirmado): ?>
                    <span class="badge-estado badge-estado--activa">
                        <i class="fa-solid fa-circle-check"></i> Cuenta Activa
                    </span>
                <?php else: ?>
                    <span class="badge-estado badge-estado--pendiente">
                        <i class="fa-solid fa-clock"></i> Pendiente
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="ficha-alumno-perfil__acciones">
        <a href="/admin/rutinas/crear?cliente_id=<?php echo $alumno->id; ?>" class="boton">
            <i class="fa-solid fa-dumbbell"></i> Diseñar Rutina Personalizada
        </a>
    </div>
</div>

<!-- Métricas de Asistencia con Este Profesor -->
<div class="agenda-metricas-grid">
    <div class="metrica-card metrica-card--borde-azul">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Clases Contigo</span>
            <strong class="metrica-card__valor"><?php echo $stats['totalReservas']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-verde">
        <div class="metrica-card__icono metrica-card__icono--verde">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Asistencias Confirmadas</span>
            <strong class="metrica-card__valor metrica-card__valor--verde"><?php echo $stats['totalAsistencias']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-rojo">
        <div class="metrica-card__icono metrica-card__icono--rojo">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
        <div>
            <span class="metrica-card__label">Ausencias Registradas</span>
            <strong class="metrica-card__valor metrica-card__valor--rojo"><?php echo $stats['totalAusencias']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-naranja">
        <div class="metrica-card__icono metrica-card__icono--naranja">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div>
            <span class="metrica-card__label">Presentismo en tus Clases</span>
            <strong class="metrica-card__valor"><?php echo $stats['presentismo']; ?>%</strong>
        </div>
    </div>
</div>

<!-- Membresía Actual -->
<div class="ficha-seccion">
    <h3 class="ficha-seccion__titulo">
        <i class="fa-solid fa-id-card"></i> Estado de Membresía
    </h3>

    <?php if(!empty($membresias)): ?>
        <?php foreach($membresias as $membresia): ?>
            <div class="ficha-membresia-card" style="margin-bottom: 1.5rem;">
                <div class="ficha-membresia-card__info">
                    <h4>Plan Actual: <?php echo s($membresia['plan_nombre']); ?></h4>
                    <p>
                        Vigencia: <?php echo date('d/m/Y', strtotime($membresia['fecha_inicio'])); ?> 
                        al <?php echo date('d/m/Y', strtotime($membresia['fecha_fin'])); ?>
                    </p>
                </div>
                <div class="ficha-membresia-card__badge">
                    <i class="fa-solid fa-circle-check"></i> Membresía Activa
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="ficha-membresia-card ficha-membresia-card--inactiva">
            <div class="ficha-membresia-card__info">
                <h4>Sin Membresía Activa</h4>
                <p>El alumno no tiene un plan activo asignado actualmente.</p>
            </div>
            <span class="badge-membresia badge-membresia--sin-plan">
                Sin Plan
            </span>
        </div>
    <?php endif; ?>
</div>

<!-- Rutinas Asignadas por este Entrenador -->
<div class="ficha-seccion">
    <h3 class="ficha-seccion__titulo">
        <i class="fa-solid fa-dumbbell"></i> Rutinas y WODs Diseñados por Vos
    </h3>

    <?php if(empty($rutinas)): ?>
        <div class="agenda-vacia-card">
            <i class="fa-solid fa-clipboard-list icono-muted"></i>
            <h3>No le has asignado rutinas personalizadas aún</h3>
            <p>Podés diseñar un entrenamiento individual o WOD adaptado a los objetivos de este alumno.</p>
            <a href="/admin/rutinas/crear?cliente_id=<?php echo $alumno->id; ?>" class="boton">
                <i class="fa-solid fa-plus"></i> Crear Rutina Personalizada
            </a>
        </div>
    <?php else: ?>
        <div class="ficha-rutinas-grid">
            <?php foreach($rutinas as $rutina): ?>
                <article class="card-rutina-alumno">
                    <h4 class="card-rutina-alumno__titulo">
                        <?php echo s($rutina['nombre'] ?: $rutina['plan_nombre']); ?>
                    </h4>
                    <div class="card-rutina-alumno__meta">
                        <div><i class="fa-solid fa-layer-group"></i> <?php echo s($rutina['plan_nombre']); ?> (<?php echo s(ucfirst($rutina['tipo_formato'])); ?>)</div>
                        <?php if(!empty($rutina['fecha'])): ?>
                            <div><i class="fa-regular fa-calendar"></i> <?php echo date('d/m/Y', strtotime($rutina['fecha'])); ?></div>
                        <?php endif; ?>
                    </div>
                    <a href="/admin/rutinas/detalle?id=<?php echo $rutina['id']; ?>" class="card-rutina-alumno__link">
                        Ver Rutina Completa <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Historial de Clases con este Entrenador -->
<div class="ficha-seccion">
    <h3 class="ficha-seccion__titulo">
        <i class="fa-solid fa-clock-rotate-left"></i> Historial de Clases Contigo
    </h3>

    <?php if(empty($clases)): ?>
        <p class="turnos-seccion__vacio-texto">Este alumno aún no ha asistido a clases a tu cargo.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Disciplina</th>
                        <th class="tabla__th--centro">Estado / Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clases as $clase): 
                        $timestamp = strtotime($clase['fecha']);
                        $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        $diaNombre = $diasSemana[(int)date('w', $timestamp)];
                        $fechaFormato = date('d/m/Y', $timestamp);
                        $esCancelada = ($clase['reserva_estado'] === 'cancelada');
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo $diaNombre; ?></strong> <?php echo $fechaFormato; ?>
                            </td>
                            <td>
                                <?php echo s(substr($clase['hora_inicio'], 0, 5)); ?> - <?php echo s(substr($clase['hora_fin'], 0, 5)); ?> hs
                            </td>
                            <td>
                                <strong><?php echo s($clase['plan_nombre']); ?></strong>
                                <?php if(!empty($clase['descripcion'])): ?>
                                    <span class="subtexto-descripcion"><?php echo s($clase['descripcion']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="tabla__td--centro">
                                <?php if($esCancelada): ?>
                                    <span class="badge-asistencia badge-asistencia--cancelada">
                                        <i class="fa-solid fa-ban"></i> Cancelada
                                    </span>
                                <?php elseif((string)$clase['asistencia_presente'] === '1'): ?>
                                    <span class="badge-asistencia badge-asistencia--presente">
                                        <i class="fa-solid fa-circle-check"></i> Presente
                                    </span>
                                <?php elseif((string)$clase['asistencia_presente'] === '0'): ?>
                                    <span class="badge-asistencia badge-asistencia--ausente">
                                        <i class="fa-solid fa-circle-xmark"></i> Ausente
                                    </span>
                                <?php else: ?>
                                    <span class="badge-asistencia badge-asistencia--pendiente">
                                        <i class="fa-regular fa-clock"></i> Pendiente
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
