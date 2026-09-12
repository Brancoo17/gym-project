<?php
/** @var string $fecha */
/** @var int $diaSemana */
/** @var string $fechaAnterior */
/** @var string $fechaSiguiente */
/** @var bool $esHoy */
/** @var array $horarios */
/** @var array $reservasPorHorario */
/** @var int $totalClases */
/** @var int $totalAlumnos */
/** @var int $capacidadTotal */
/** @var int $porcentajeOcupacion */

$timestamp = strtotime($fecha);
$diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$diaNombre = $diasSemana[(int)date('w', $timestamp)];
$diaNumero = date('j', $timestamp);
$mesNombre = $meses[(int)date('n', $timestamp)];
$anio = date('Y', $timestamp);
$fechaTexto = "{$diaNombre} {$diaNumero} de {$mesNombre} de {$anio}";
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Agenda de Reservas</h1>
        <p>Consultá las clases programadas y la asistencia de alumnos por turno.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/admin/horarios" class="boton-accion">
            <i class="fa-solid fa-clock"></i> Administrar Horarios
        </a>
    </div>
</div>

<!-- Barra de Navegación de Fechas (Sin botón Consultar) -->
<div class="agenda-nav-card">
    <div class="agenda-nav-top">
        <div class="agenda-fecha-display">
            <i class="fa-solid fa-calendar-day"></i>
            <div>
                <div class="agenda-fecha-display__titulo-grupo">
                    <h2><?php echo $fechaTexto; ?></h2>
                    <?php if($esHoy): ?>
                        <span class="badge-hoy"><i class="fa-solid fa-circle"></i> Hoy</span>
                    <?php endif; ?>
                </div>
                <span>Día de clases: <strong><?php echo $diaNombre; ?></strong></span>
            </div>
        </div>

        <div class="agenda-nav-controles">
            <a href="/admin/reservas?fecha=<?php echo $fechaAnterior; ?>" class="btn-nav-dia" title="Día Anterior">
                <i class="fa-solid fa-chevron-left"></i> Anterior
            </a>

            <?php if(!$esHoy): ?>
                <a href="/admin/reservas?fecha=<?php echo date('Y-m-d'); ?>" class="btn-nav-hoy" title="Ir al día de hoy">
                    <i class="fa-solid fa-calendar-check"></i> Hoy
                </a>
            <?php endif; ?>

            <div class="input-fecha-wrapper">
                <input type="date" 
                       id="selector_fecha" 
                       value="<?php echo s($fecha); ?>" 
                       title="Cambiar fecha"
                       onchange="window.location.href = '/admin/reservas?fecha=' + this.value">
            </div>

            <a href="/admin/reservas?fecha=<?php echo $fechaSiguiente; ?>" class="btn-nav-dia" title="Día Siguiente">
                Siguiente <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Métricas Resumen del Día -->
<div class="agenda-metricas-grid">
    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--naranja">
            <i class="fa-solid fa-dumbbell"></i>
        </div>
        <div>
            <span class="metrica-card__label">Clases del Día</span>
            <strong class="metrica-card__valor"><?php echo $totalClases; ?></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--azul">
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
            <strong class="metrica-card__valor"><?php echo $totalPresentes; ?> <span class="metrica-card__subvalor">/ <?php echo $totalAlumnos; ?></span></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--celeste">
            <i class="fa-solid fa-clipboard-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Tasa de Presentismo</span>
            <strong class="metrica-card__valor"><?php echo $tasaAsistenciaDia; ?>%</strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--gris">
            <i class="fa-solid fa-ticket"></i>
        </div>
        <div>
            <span class="metrica-card__label">Capacidad de Cupos</span>
            <strong class="metrica-card__valor"><?php echo $totalAlumnos; ?> / <?php echo !empty($tieneIlimitados) ? ($capacidadTotal . '+') : $capacidadTotal; ?></strong>
        </div>
    </div>
</div>

<!-- Buscador en tiempo real de alumnos/clases -->
<?php if(!empty($horarios)): ?>
    <div class="agenda-buscador">
        <div class="agenda-buscador__input-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" 
                   id="filtro_busqueda" 
                   placeholder="Filtrar por alumno, clase o profesor..." 
                   onkeyup="filtrarAgenda(this.value)">
        </div>
    </div>
<?php endif; ?>

<!-- Contenedor Principal de la Agenda -->
<?php if($diaSemana === 0): ?>
    <div class="agenda-vacia-card agenda-vacia-card--cerrado">
        <i class="fa-solid fa-calendar-xmark"></i>
        <h3>Gimnasio Cerrado</h3>
        <p>Los días domingo no hay clases programadas en el gimnasio.</p>
        <a href="/admin/reservas?fecha=<?php echo $fechaSiguiente; ?>" class="boton">
            Ver clases de mañana Lunes <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
<?php elseif(empty($horarios)): ?>
    <div class="agenda-vacia-card agenda-vacia-card--sin-clases">
        <i class="fa-solid fa-calendar-minus"></i>
        <h3>No hay clases programadas para este día</h3>
        <p>No se encontraron horarios activos para los días <?php echo $diaNombre; ?>.</p>
        <a href="/admin/horarios/crear" class="boton">
            <i class="fa-solid fa-plus"></i> Programar Clases
        </a>
    </div>
<?php else: ?>
    <div id="contenedor_clases" class="contenedor-clases-agenda">
        <?php foreach($horarios as $h): ?>
            <?php 
                $inscriptos = $reservasPorHorario[$h->id] ?? [];
                $cantidadInscriptos = count($inscriptos);
                $cantidadPresentes = count(array_filter($inscriptos, fn($r) => (int)$r->asistencia_presente === 1));
                $esIlimitado = is_null($h->cupo) || $h->cupo === '';
                $cupoTotal = $esIlimitado ? null : (int)$h->cupo;
                $lugaresLibres = $esIlimitado ? null : max(0, $cupoTotal - $cantidadInscriptos);
                $pctOcupacion = (!$esIlimitado && $cupoTotal > 0) ? min(100, round(($cantidadInscriptos / $cupoTotal) * 100)) : 0;
                $estaCompleta = !$esIlimitado && ($cantidadInscriptos >= $cupoTotal);
                $horaInicio = substr($h->hora_inicio, 0, 5);
                $horaFin = substr($h->hora_fin, 0, 5);
            ?>

            <section class="clase-agenda-card" data-clase="<?php echo strtolower($h->plan_nombre); ?>" data-profesor="<?php echo strtolower($h->entrenador); ?>">
                <!-- Cabecera de la Clase -->
                <div class="clase-agenda-header">
                    <div class="clase-agenda-info">
                        <div class="clase-agenda-hora">
                            <i class="fa-regular fa-clock"></i>
                            <span><?php echo $horaInicio; ?> - <?php echo $horaFin; ?> hs</span>
                        </div>
                        <h3 class="clase-agenda-titulo"><?php echo s($h->plan_nombre); ?></h3>
                        <span class="clase-agenda-profesor">
                            <i class="fa-solid fa-user-tie"></i> Prof. <?php echo s($h->entrenador); ?>
                        </span>
                        <?php if(!empty($h->descripcion)): ?>
                            <span class="tag-modalidad" title="Modalidad / Observaciones">
                                <i class="fa-solid fa-circle-info"></i> <?php echo s($h->descripcion); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="clase-agenda-acciones-top">
                        <div class="clase-agenda-stats">
                            <div class="clase-agenda-stats__conteo">
                                <span class="clase-agenda-inscriptos-text">
                                    <?php if($esIlimitado): ?>
                                        <strong><?php echo $cantidadInscriptos; ?></strong> alumnos (Ilimitado)
                                    <?php else: ?>
                                        <strong><?php echo $cantidadInscriptos; ?></strong> / <?php echo $cupoTotal; ?> alumnos
                                    <?php endif; ?>
                                    <span class="contador-presentes-clase">
                                        <i class="fa-solid fa-user-check"></i> <?php echo $cantidadPresentes; ?> presentes
                                    </span>
                                </span>
                                <div class="clase-agenda-status-wrapper">
                                    <?php if($esIlimitado): ?>
                                        <span class="badge-status badge-status--disponible">Cupo Ilimitado</span>
                                    <?php elseif($estaCompleta): ?>
                                        <span class="badge-status badge-status--llena"><i class="fa-solid fa-lock"></i> Clase Completa</span>
                                    <?php elseif($cantidadInscriptos === 0): ?>
                                        <span class="badge-status badge-status--vacia">Sin inscriptos (<?php echo $lugaresLibres; ?> libres)</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-status--disponible"><?php echo $lugaresLibres; ?> lugares disponibles</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Barra de progreso de cupos -->
                            <?php if(!$esIlimitado): ?>
                                <div class="progreso-cupos-bar" title="<?php echo $pctOcupacion; ?>% ocupado">
                                    <div class="progreso-cupos-fill <?php echo $estaCompleta ? 'progreso-cupos-fill--full' : ''; ?>" style="width: <?php echo $pctOcupacion; ?>%;"></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($inscriptos)): ?>
                            <button type="button" 
                                    class="btn-toggle-alumnos" 
                                    data-target="alumnos-clase-<?php echo $h->id; ?>"
                                    title="Desplegar u ocultar lista de alumnos">
                                <span class="btn-toggle-texto">Ver Alumnos</span>
                                <i class="fa-solid fa-chevron-down flecha-toggle"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista de Alumnos Inscriptos (Desplegable) -->
                <?php if(!empty($inscriptos)): ?>
                    <div class="clase-agenda-body" id="alumnos-clase-<?php echo $h->id; ?>" style="display: none;">
                        <div class="tabla-contenedor tabla-contenedor--agenda">
                            <table class="tabla tabla--sin-margen">
                                <thead>
                                    <tr>
                                        <th class="col-centrada-sm">#</th>
                                        <th>Alumno</th>
                                        <th>Contacto / Email</th>
                                        <th>Teléfono</th>
                                        <th class="col-asistencia-head">Asistencia</th>
                                        <th class="col-accion-head">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach($inscriptos as $reserva): ?>
                                        <tr class="fila-alumno" data-alumno="<?php echo strtolower($reserva->cliente_nombre); ?>" data-email="<?php echo strtolower($reserva->cliente_email); ?>">
                                            <td class="col-centrada-sm">
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
                                            <td class="col-centrada">
                                                <div class="control-asistencia" data-reserva-id="<?php echo $reserva->id; ?>">
                                                    <button type="button" 
                                                            class="btn-asistencia-item btn-asistencia-item--presente <?php echo ((int)$reserva->asistencia_presente === 1) ? 'activo' : ''; ?>" 
                                                            data-valor="1"
                                                            title="Marcar como presente">
                                                        <i class="fa-solid fa-check"></i> Presente
                                                    </button>
                                                    <button type="button" 
                                                            class="btn-asistencia-item btn-asistencia-item--ausente <?php echo ((string)$reserva->asistencia_presente === '0') ? 'activo' : ''; ?>" 
                                                            data-valor="0"
                                                            title="Marcar como ausente">
                                                        <i class="fa-solid fa-xmark"></i> Ausente
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="col-centrada">
                                                <button type="button" 
                                                        class="btn-cancelar-admin" 
                                                        data-id="<?php echo $reserva->id; ?>" 
                                                        data-nombre="<?php echo s($reserva->cliente_nombre); ?>"
                                                        data-clase="<?php echo s($h->plan_nombre); ?>"
                                                        title="Cancelar reserva de este alumno">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
