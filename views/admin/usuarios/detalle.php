<?php
/** @var \Model\Usuario $usuario */
/** @var array $stats */
/** @var array|null $membresia */
/** @var array $reservas */
/** @var array $rutinas */

$nombreCompleto = $usuario->nombre . ' ' . $usuario->apellido;
$iniciales = strtoupper(substr($usuario->nombre, 0, 1) . substr($usuario->apellido, 0, 1));
$esConfirmado = ((int)$usuario->confirmado === 1);
$telLimpio = preg_replace('/[^0-9]/', '', $usuario->telefono ?? '');
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Ficha del Alumno</h1>
        <p>Seguimiento individual, constancia de asistencia y entrenamientos asignados.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/admin/usuarios" class="boton">
            <i class="fa-solid fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</div>

<?php if((int)($resultado ?? 0) === 5): ?>
    <div class="alerta exito" style="margin-bottom: 2rem;">
        <i class="fa-solid fa-circle-check"></i>
        <span>Membresía asignada y cobro en efectivo registrado exitosamente.</span>
    </div>
<?php endif; ?>

<!-- Perfil Principal del Alumno -->
<div class="ficha-alumno-perfil">
    <div class="ficha-alumno-perfil__principal">
        <div class="ficha-alumno-perfil__avatar">
            <?php echo $iniciales; ?>
        </div>
        <div class="ficha-alumno-perfil__info">
            <h2><?php echo s($nombreCompleto); ?></h2>
            <div class="datos-contacto">
                <?php if(!empty($usuario->dni)): ?>
                    <span class="contacto-item">
                        <i class="fa-solid fa-id-card"></i> DNI: <strong><?php echo s($usuario->dni); ?></strong>
                    </span>
                <?php else: ?>
                    <span class="badge-estado badge-estado--sin-dni">
                        <i class="fa-solid fa-id-card"></i> Sin DNI
                    </span>
                <?php endif; ?>

                <a href="mailto:<?php echo s($usuario->email); ?>">
                    <i class="fa-regular fa-envelope"></i> <?php echo s($usuario->email); ?>
                </a>

                <?php if(!empty($usuario->telefono)): ?>
                    <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($usuario->nombre); ?>%2C%20te%20escribimos%20desde%20el%20gimnasio." 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="contacto-whatsapp">
                        <i class="fa-brands fa-whatsapp"></i> <?php echo s($usuario->telefono); ?>
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
                        <i class="fa-solid fa-clock"></i> Pendiente de Confirmar
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="ficha-alumno-perfil__acciones">
        <a href="/admin/usuarios/actualizar?id=<?php echo $usuario->id; ?>" class="boton">
            <i class="fa-solid fa-pen-to-square"></i> Editar Alumno
        </a>

        <form method="POST" action="/admin/usuarios/eliminar" onsubmit="return confirm('¿Estás seguro de que deseas eliminar permanentemente a <?php echo s($nombreCompleto); ?>? Se borrarán sus reservas y registros asociados.');">
            <input type="hidden" name="id" value="<?php echo $usuario->id; ?>">
            <button type="submit" class="boton-eliminar">
                <i class="fa-solid fa-trash-can"></i> Eliminar Alumno
            </button>
        </form>
    </div>
</div>

<!-- Métricas Rápidas del Alumno -->
<div class="agenda-metricas-grid">
    <div class="metrica-card metrica-card--borde-azul">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Total Turnos Reservados</span>
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
            <span class="metrica-card__label">Porcentaje de Presentismo</span>
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
        <div class="planes-cards-grid" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
        <?php foreach($membresias as $membresia): 
            $dias = isset($membresia['dias_restantes']) ? (int)$membresia['dias_restantes'] : null;
        ?>
            <div class="ficha-membresia-card" style="margin-bottom: 0;">
                <div class="ficha-membresia-card__info">
                    <h4>Plan: <?php echo s($membresia['plan_nombre']); ?></h4>
                    <p>
                        Vigencia: <?php echo date('d/m/Y', strtotime($membresia['fecha_inicio'])); ?> 
                        al <?php echo date('d/m/Y', strtotime($membresia['fecha_fin'])); ?>
                    </p>
                    <?php if($dias !== null && $dias >= 0 && $dias <= 7): ?>
                        <div style="margin-top: 0.5rem;">
                            <span class="badge <?php echo $dias === 0 ? 'badge--rojo' : 'badge--amarillo'; ?>" style="font-size: 1.25rem;">
                                <i class="fa-solid fa-clock"></i> <?php echo $dias === 0 ? '¡Vence hoy!' : ($dias === 1 ? 'Vence mañana' : "Vence en {$dias} días"); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ficha-membresia-card__badge" style="background: var(--verde); color: var(--blanco); padding: 0.5rem 1rem; border-radius: 0.5rem;">
                    <i class="fa-solid fa-circle-check"></i> Activa
                </div>
            </div>
        <?php endforeach; ?>
        </div>
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
    
    <div style="margin-top: 2rem;">
        <a href="/admin/usuarios/membresia-manual?id=<?php echo $usuario->id; ?>" class="boton">
            <i class="fa-solid fa-money-bill"></i> Activar / Renovar Manualmente (Efectivo)
        </a>
    </div>
</div>

<!-- Rutinas Personalizadas Asignadas -->
<div class="ficha-seccion">
    <h3 class="ficha-seccion__titulo">
        <i class="fa-solid fa-dumbbell"></i> Rutinas y WODs Personalizados
    </h3>

    <?php if(empty($rutinas)): ?>
        <div class="agenda-vacia-card">
            <i class="fa-solid fa-clipboard-list icono-muted"></i>
            <h3>No hay rutinas personalizadas asignadas</h3>
            <p>Este alumno entrena con los entrenamientos generales de cada disciplina o aún no se le diseñó una rutina individual.</p>
            <a href="/admin/rutinas/crear" class="boton">
                <i class="fa-solid fa-plus"></i> Asignar Rutina Personalizada
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
                        <div><i class="fa-solid fa-user-tie"></i> Prof. <?php echo s($rutina['entrenador_nombre']); ?></div>
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

<!-- Historial de Clases y Asistencias -->
<div class="ficha-seccion">
    <h3 class="ficha-seccion__titulo">
        <i class="fa-solid fa-clock-rotate-left"></i> Historial de Clases y Reservas
    </h3>

    <?php if(empty($reservas)): ?>
        <p class="turnos-seccion__vacio-texto">El alumno aún no ha reservado turnos en el sistema.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Disciplina</th>
                        <th>Profesor</th>
                        <th class="tabla__th--centro">Estado / Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reservas as $reserva): 
                        $timestamp = strtotime($reserva['fecha']);
                        $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        $diaNombre = $diasSemana[(int)date('w', $timestamp)];
                        $fechaFormato = date('d/m/Y', $timestamp);
                        $esCancelada = ($reserva['reserva_estado'] === 'cancelada');
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo $diaNombre; ?></strong> <?php echo $fechaFormato; ?>
                            </td>
                            <td>
                                <?php echo s(substr($reserva['hora_inicio'], 0, 5)); ?> - <?php echo s(substr($reserva['hora_fin'], 0, 5)); ?> hs
                            </td>
                            <td>
                                <strong><?php echo s($reserva['plan_nombre']); ?></strong>
                                <?php if(!empty($reserva['descripcion'])): ?>
                                    <span class="subtexto-descripcion"><?php echo s($reserva['descripcion']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo s($reserva['entrenador_nombre']); ?>
                            </td>
                            <td class="tabla__td--centro">
                                <?php if($esCancelada): ?>
                                    <span class="badge-asistencia badge-asistencia--cancelada">
                                        <i class="fa-solid fa-ban"></i> Cancelada
                                    </span>
                                <?php elseif((string)$reserva['asistencia_presente'] === '1'): ?>
                                    <span class="badge-asistencia badge-asistencia--presente">
                                        <i class="fa-solid fa-circle-check"></i> Presente
                                    </span>
                                <?php elseif((string)$reserva['asistencia_presente'] === '0'): ?>
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
