<?php
/** @var string $nombre */
/** @var string $fecha */
/** @var int $diaSemana */
/** @var string $fechaAnterior */
/** @var string $fechaSiguiente */
/** @var bool $esHoy */
/** @var array $horarios */
/** @var array $reservasPorHorario */
/** @var int $totalClases */
/** @var int $totalAlumnos */
/** @var int $totalPresentes */

$diasNombres = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$mesesNombres = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$timestampFecha = strtotime($fecha);
$diaNombre = $diasNombres[$diaSemana] ?? '';
$diaNumero = date('j', $timestampFecha);
$mesNombre = $mesesNombres[(int)date('n', $timestampFecha)] ?? '';
$anio = date('Y', $timestampFecha);
?>

<div class="turnos-header">
    <div>
        <h1>Mis Clases y Control de Asistencia</h1>
        <p>Consultá tus clases y pasá lista a tus alumnos inscriptos para registrar su asistencia.</p>
    </div>
    <a href="/entrenador" class="boton-accion btn-volver-panel">
        <i class="fa-solid fa-arrow-left"></i> Volver al Panel
    </a>
</div>

<!-- ========================================== -->
<!-- BARRA DE NAVEGACIÓN DE FECHAS -->
<!-- ========================================== -->
<div class="agenda-nav-card">
    <div class="agenda-nav-top">
        <div class="agenda-fecha-display">
            <i class="fa-solid fa-calendar-check"></i>
            <div>
                <h2><?php echo $diaNombre; ?>, <?php echo $diaNumero; ?> de <?php echo $mesNombre; ?> <?php echo $anio; ?></h2>
                <span>Profesor a cargo: <strong><?php echo s($nombre); ?></strong></span>
            </div>
            <?php if($esHoy): ?>
                <span class="badge-hoy"><i class="fa-solid fa-circle"></i> Hoy</span>
            <?php endif; ?>
        </div>

        <div class="agenda-nav-controles">
            <a href="/entrenador/asistencia?fecha=<?php echo $fechaAnterior; ?>" class="btn-nav-dia" title="Ver día anterior">
                <i class="fa-solid fa-chevron-left"></i> Anterior
            </a>

            <?php if(!$esHoy): ?>
                <a href="/entrenador/asistencia" class="btn-nav-hoy" title="Ir al día de hoy">
                    <i class="fa-solid fa-calendar-day"></i> Hoy
                </a>
            <?php endif; ?>

            <a href="/entrenador/asistencia?fecha=<?php echo $fechaSiguiente; ?>" class="btn-nav-dia" title="Ver día siguiente">
                Siguiente <i class="fa-solid fa-chevron-right"></i>
            </a>

            <div class="input-fecha-wrapper">
                <input type="date" 
                       id="selector_fecha_entrenador" 
                       value="<?php echo $fecha; ?>" 
                       title="Elegir fecha específica"
                       onchange="location.href='/entrenador/asistencia?fecha=' + this.value">
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MÉTRICAS RÁPIDAS DE LA JORNADA -->
<!-- ========================================== -->
<div class="agenda-metricas-grid">
    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-dumbbell"></i>
        </div>
        <div>
            <span class="metrica-card__label">Clases a tu cargo</span>
            <strong class="metrica-card__valor"><?php echo $totalClases; ?></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--naranja">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span class="metrica-card__label">Alumnos Inscriptos</span>
            <strong class="metrica-card__valor"><?php echo $totalAlumnos; ?></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--verde">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Presentes Confirmados</span>
            <strong class="metrica-card__valor" id="total_presentes_kpi"><?php echo $totalPresentes; ?></strong>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- LISTA DE CLASES Y ALUMNOS -->
<!-- ========================================== -->
<?php if(empty($horarios)): ?>
    <div class="agenda-vacia-card">
        <i class="fa-regular fa-calendar-xmark icono-muted"></i>
        <h3>No tenés clases asignadas para este día</h3>
        <p>Seleccioná otra fecha desde el calendario o consultá la grilla semanal con la administración.</p>
        <?php if(!$esHoy): ?>
            <a href="/entrenador/asistencia" class="boton btn-volver-hoy">Volver a Hoy</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div id="contenedor_clases" class="clases-contenedor">
        <?php foreach($horarios as $h): ?>
            <?php 
                $inscriptos = $reservasPorHorario[$h->id] ?? [];
                $cantidadInscriptos = count($inscriptos);
                $cantidadPresentes = count(array_filter($inscriptos, fn($r) => (int)$r->asistencia_presente === 1));
                $horaInicio = substr($h->hora_inicio, 0, 5);
                $horaFin = substr($h->hora_fin, 0, 5);
            ?>

            <section class="clase-agenda-card">
                <!-- Cabecera de la Clase -->
                <div class="clase-agenda-header">
                    <div class="clase-agenda-info">
                        <div class="clase-agenda-hora">
                            <i class="fa-regular fa-clock"></i>
                            <span><?php echo $horaInicio; ?> - <?php echo $horaFin; ?> hs</span>
                        </div>
                        <h3 class="clase-agenda-titulo"><?php echo s($h->plan_nombre); ?></h3>
                        <?php if(!empty($h->descripcion)): ?>
                            <span class="tag-modalidad" title="Modalidad">
                                <i class="fa-solid fa-circle-info"></i> <?php echo s($h->descripcion); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="clase-agenda-acciones-top">
                        <div class="clase-agenda-stats">
                            <div class="clase-agenda-stats__conteo">
                                <span class="clase-agenda-inscriptos-text">
                                    <strong><?php echo $cantidadInscriptos; ?></strong> alumnos inscriptos
                                    <span class="contador-presentes-clase">
                                        <i class="fa-solid fa-user-check"></i> <?php echo $cantidadPresentes; ?> presentes
                                    </span>
                                </span>
                            </div>
                        </div>

                        <?php if(!empty($inscriptos)): ?>
                            <button type="button" 
                                    class="btn-toggle-alumnos activo" 
                                    data-target="alumnos-clase-<?php echo $h->id; ?>"
                                    title="Desplegar u ocultar lista de alumnos">
                                <span class="btn-toggle-texto">Ocultar Alumnos</span>
                                <i class="fa-solid fa-chevron-up flecha-toggle"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de Alumnos Inscriptos para pasar lista -->
                <div class="clase-agenda-body" id="alumnos-clase-<?php echo $h->id; ?>">
                    <?php if(empty($inscriptos)): ?>
                        <div class="clase-vacia-mensaje">
                            <i class="fa-regular fa-user"></i>
                            <strong>No hay alumnos inscriptos para esta clase en esta fecha.</strong>
                        </div>
                    <?php else: ?>
                        <div class="tabla-contenedor tabla-contenedor--sin-margen">
                            <table class="tabla tabla--sin-margen">
                                <thead>
                                    <tr>
                                        <th class="tabla__th--indice">#</th>
                                        <th>Alumno</th>
                                        <th>Contacto / Email</th>
                                        <th>Teléfono</th>
                                        <th class="tabla__th--presentismo">Presentismo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach($inscriptos as $reserva): ?>
                                        <tr class="fila-alumno">
                                            <td class="tabla__td--indice">
                                                <?php echo $i++; ?>
                                            </td>
                                            <td>
                                                <div class="alumno-fila-item">
                                                    <div class="avatar-alumno">
                                                        <?php echo strtoupper(substr($reserva->cliente_nombre, 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong class="alumno-nombre">
                                                            <?php echo s($reserva->cliente_nombre); ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="alumno-email">
                                                    <i class="fa-regular fa-envelope"></i>
                                                    <?php echo s($reserva->cliente_email); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if(!empty($reserva->cliente_telefono)): ?>
                                                    <?php 
                                                        $telLimpio = preg_replace('/[^0-9]/', '', $reserva->cliente_telefono); 
                                                    ?>
                                                    <a href="https://wa.me/<?php echo $telLimpio; ?>" 
                                                       target="_blank" 
                                                       rel="noopener noreferrer" 
                                                       class="btn-whatsapp" 
                                                       title="Enviar mensaje por WhatsApp">
                                                        <i class="fa-brands fa-whatsapp"></i> <?php echo s($reserva->cliente_telefono); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="alumno-sin-tel">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="tabla__td--centro">
                                                <div class="control-asistencia" data-reserva-id="<?php echo $reserva->id; ?>">
                                                    <button type="button" 
                                                            class="btn-asistencia-item btn-asistencia-item--presente <?php echo ((int)$reserva->asistencia_presente === 1) ? 'activo' : ''; ?>" 
                                                            data-valor="1"
                                                            title="Marcar asistencia">
                                                        <i class="fa-solid fa-check"></i> Presente
                                                    </button>
                                                    <button type="button" 
                                                            class="btn-asistencia-item btn-asistencia-item--ausente <?php echo ((string)$reserva->asistencia_presente === '0') ? 'activo' : ''; ?>" 
                                                            data-valor="0"
                                                            title="Marcar ausencia">
                                                        <i class="fa-solid fa-xmark"></i> Ausente
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

