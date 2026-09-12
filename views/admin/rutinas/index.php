<?php
/** @var array $rutinas */
/** @var array $planes */
/** @var mixed $planSeleccionado */
/** @var mixed $fechaSeleccionada */
/** @var mixed $resultado */
$mensaje = $resultado ? obtenerMensaje((int)$resultado) : '';
?>

<div class="ejercicios-header">
    <div>
        <h1>Rutinas y WODs Diarios</h1>
        <p>Gestioná los entrenamientos diarios para cada disciplina (Crossfit, Musculación, Funcional) y planes personalizados.</p>
    </div>
    <a href="/admin/rutinas/crear" class="boton">
        <i class="fa-solid fa-plus"></i> Crear Nueva Rutina / WOD
    </a>
</div>

<!-- Mensajes de feedback -->
<?php if($mensaje): ?>
    <div class="alerta exito">
        <i class="fa-solid fa-circle-check"></i>
        <span>Rutina <?php echo s($mensaje); ?></span>
    </div>
<?php endif; ?>

<!-- Toolbar con selector de Plan y Filtro de Fecha -->
<div class="rutinas-toolbar">
    <div class="rutinas-toolbar__filtros">
        <a href="/admin/rutinas" class="filtro-chip <?php echo empty($planSeleccionado) ? 'activo' : ''; ?>">
            <i class="fa-solid fa-layer-group"></i> Todos los Planes
        </a>
        <?php foreach($planes as $p): ?>
            <a href="/admin/rutinas?plan_id=<?php echo $p->id; ?><?php echo $fechaSeleccionada ? '&fecha=' . $fechaSeleccionada : ''; ?>" 
               class="filtro-chip <?php echo (int)$planSeleccionado === (int)$p->id ? 'activo' : ''; ?>">
                <?php echo s($p->nombre); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" action="/admin/rutinas" class="rutinas-toolbar__fecha">
        <?php if($planSeleccionado): ?>
            <input type="hidden" name="plan_id" value="<?php echo $planSeleccionado; ?>">
        <?php endif; ?>
        <i class="fa-regular fa-calendar" style="color: #149b2b;"></i>
        <input type="date" 
               name="fecha" 
               value="<?php echo s($fechaSeleccionada); ?>" 
               onchange="this.form.submit()" 
               title="Filtrar por fecha">
        <?php if($fechaSeleccionada): ?>
            <a href="/admin/rutinas<?php echo $planSeleccionado ? '?plan_id=' . $planSeleccionado : ''; ?>" 
               style="color: #94a3b8; margin-left: 0.5rem; text-decoration: none;" 
               title="Quitar filtro de fecha">
                <i class="fa-solid fa-xmark"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if(empty($rutinas)): ?>
    <div class="agenda-vacia-card">
        <i class="fa-solid fa-dumbbell" style="color: #cbd5e1;"></i>
        <h3>No se encontraron rutinas cargadas</h3>
        <p>Comenzá diseñando la primera rutina o WOD para tus clases.</p>
        <a href="/admin/rutinas/crear" class="boton">
            <i class="fa-solid fa-plus"></i> Crear Primer Entrenamiento
        </a>
    </div>
<?php else: ?>
    <!-- Grilla de Tarjetas de Rutina -->
    <div class="grid-rutinas">
        <?php foreach($rutinas as $rutina): 
            $esCrossfit = stripos($rutina->plan_nombre, 'crossfit') !== false;
            $badgeClase = $esCrossfit ? 'card-rutina__plan-badge--crossfit' : 
                          (stripos($rutina->plan_nombre, 'musculación') !== false ? 'card-rutina__plan-badge--musculacion' : 'card-rutina__plan-badge--funcional');
        ?>
            <article class="card-rutina">
                <div class="card-rutina__header">
                    <span class="card-rutina__plan-badge <?php echo $badgeClase; ?>">
                        <i class="fa-solid fa-fire-flame-curved"></i> <?php echo s($rutina->plan_nombre); ?>
                    </span>
                    <span class="card-rutina__fecha">
                        <i class="fa-regular fa-calendar"></i> <?php echo date('d/m/Y', strtotime($rutina->fecha)); ?>
                    </span>
                </div>

                <div class="card-rutina__cuerpo">
                    <h3><?php echo s($rutina->nombre); ?></h3>

                    <div class="card-rutina__meta">
                        <div class="card-rutina__meta-item">
                            <i class="fa-solid fa-user-tie"></i>
                            <span>Profesor: <strong><?php echo s($rutina->entrenador_nombre); ?></strong></span>
                        </div>

                        <div class="card-rutina__meta-item">
                            <i class="fa-solid fa-users"></i>
                            <span>
                                <?php if(!empty($rutina->cliente_nombre)): ?>
                                    Alumno: <strong><?php echo s($rutina->cliente_nombre); ?></strong>
                                <?php else: ?>
                                    Destinatarios: <strong>Todos los alumnos del plan</strong>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-rutina__badges-resumen">
                        <?php if($esCrossfit && !empty($rutina->wod_formato)): ?>
                            <span class="card-rutina__tag card-rutina__tag--wod">
                                <i class="fa-solid fa-stopwatch"></i> <?php echo s(\Model\Rutina::FORMATOS_WOD[$rutina->wod_formato] ?? strtoupper($rutina->wod_formato)); ?>
                            </span>
                        <?php endif; ?>

                        <span class="card-rutina__tag">
                            <i class="fa-solid fa-dumbbell"></i> <?php echo (int)$rutina->total_ejercicios; ?> ejercicios
                        </span>

                        <span class="card-rutina__tag">
                            <i class="fa-solid fa-layer-group"></i> <?php echo (int)$rutina->total_bloques; ?> bloque/s
                        </span>
                    </div>
                </div>

                <div class="card-rutina__footer">
                    <a href="/admin/rutinas/detalle?id=<?php echo $rutina->id; ?>" 
                       class="boton" 
                       style="padding: 0.6rem 1.4rem; font-size: 1.3rem;">
                        <i class="fa-solid fa-eye"></i> Ver Pizarra
                    </a>

                    <div class="acciones-tabla">
                        <a href="/admin/rutinas/actualizar?id=<?php echo $rutina->id; ?>" 
                           class="btn-micro btn-micro--editar" 
                           title="Editar rutina">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        <form method="POST" 
                              action="/admin/rutinas/eliminar" 
                              onsubmit="return confirm('¿Seguro que deseas eliminar la rutina <?php echo s($rutina->nombre); ?>?');">
                            <input type="hidden" name="id" value="<?php echo $rutina->id; ?>">
                            <button type="submit" 
                                    class="btn-micro btn-micro--eliminar" 
                                    title="Eliminar rutina">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
