<?php
/** @var string $nombre */
/** @var array $turnosFuturos */
/** @var array $turnosPasados */
/** @var array $statsAsistencia */
$turnosFuturos = $turnosFuturos ?? [];
$turnosPasados = $turnosPasados ?? [];
$statsAsistencia = $statsAsistencia ?? [
    'totalAsistidas' => 0,
    'totalAusencias' => 0,
    'totalCompletadas' => 0,
    'porcentajeAsistencia' => 100
];

// Fallback por si se pasa la variable $turnos genérica
if(empty($turnosFuturos) && empty($turnosPasados) && !empty($turnos)) {
    $hoy = date('Y-m-d');
    foreach($turnos as $t) {
        if($t->fecha >= $hoy && $t->estado === 'reservada') {
            $turnosFuturos[] = $t;
        } else {
            $turnosPasados[] = $t;
        }
    }
    usort($turnosFuturos, function($a, $b) {
        if($a->fecha === $b->fecha) {
            return strcmp($a->hora_inicio, $b->hora_inicio);
        }
        return strcmp($a->fecha, $b->fecha);
    });
}
?>

<div class="turnos-header">
    <div>
        <h1>Mis Turnos y Asistencias</h1>
        <p>Consultá tus próximas clases reservadas, tu historial de entrenamientos y tu constancia de presentismo.</p>
    </div>
    <a href="/cliente/reservar" class="boton">
        + Reservar Nuevo Turno
    </a>
</div>

<!-- Alertas dinámicas -->
<div id="alerta_cancelacion"></div>

<!-- Métricas de Asistencia del Alumno -->
<div class="agenda-metricas-grid">
    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Próximos Turnos</span>
            <strong class="metrica-card__valor"><?php echo count($turnosFuturos); ?></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--verde">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Clases Asistidas</span>
            <strong class="metrica-card__valor"><?php echo $statsAsistencia['totalAsistidas']; ?></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--rojo">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
        <div>
            <span class="metrica-card__label">Ausencias Registradas</span>
            <strong class="metrica-card__valor metrica-card__valor--rojo"><?php echo $statsAsistencia['totalAusencias']; ?></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--celeste">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div>
            <span class="metrica-card__label">Tu Presentismo</span>
            <strong class="metrica-card__valor"><?php echo $statsAsistencia['porcentajeAsistencia']; ?>%</strong>
        </div>
    </div>
</div>

<!-- Próximos Turnos Activos -->
<div class="turnos-seccion">
    <h3 class="turnos-seccion__titulo">
        <i class="fa-regular fa-calendar-check turnos-seccion__icono--verde"></i> Próximas Clases Reservadas
    </h3>

    <?php if(empty($turnosFuturos)): ?>
        <div class="turnos-vacio">
            <i class="fa-regular fa-calendar-xmark"></i>
            <h4>No tenés clases reservadas próximamente</h4>
            <p>Asegurá tu lugar en la clase que prefieras.</p>
            <a href="/cliente/reservar" class="boton">+ Reservar Turno</a>
        </div>
    <?php else: ?>
        <div class="grid-turnos">
            <?php foreach($turnosFuturos as $turno): 
                $timestamp = strtotime($turno->fecha);
                $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $diaNombre = $diasSemana[(int)date('w', $timestamp)];
                $fechaFormato = date('d/m/Y', $timestamp);
                $yaPresente = (!is_null($turno->asistencia_presente) && (int)$turno->asistencia_presente === 1);
            ?>
                <article class="card-turno-futuro <?php echo $yaPresente ? 'card-turno-futuro--presente' : ''; ?>">
                    <div class="card-turno-futuro__header">
                        <span class="card-turno-futuro__hora">
                            <i class="fa-regular fa-clock"></i> <?php echo s(substr($turno->hora_inicio, 0, 5)); ?> - <?php echo s(substr($turno->hora_fin, 0, 5)); ?> hs
                        </span>
                        <span class="card-turno-futuro__fecha">
                            <?php echo $diaNombre; ?> <?php echo $fechaFormato; ?>
                        </span>
                    </div>

                    <div class="card-turno-futuro__top">
                        <h4 class="card-turno-futuro__titulo"><?php echo s($turno->plan_nombre); ?></h4>
                        <?php if($yaPresente): ?>
                            <span class="badge-asistencia-confirmada">
                                <i class="fa-solid fa-circle-check"></i> Presente
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(!empty($turno->descripcion)): ?>
                        <div class="tag-descripcion">
                            <i class="fa-solid fa-circle-info"></i>
                            <span><?php echo s($turno->descripcion); ?></span>
                        </div>
                    <?php endif; ?>

                    <p class="card-turno-futuro__profesor">
                        <i class="fa-solid fa-user-tie"></i> Prof. <strong><?php echo s($turno->entrenador); ?></strong>
                    </p>

                    <div class="card-turno-futuro__footer">
                        <?php if($yaPresente): ?>
                            <div class="mensaje-asistencia-confirmada">
                                <i class="fa-solid fa-check-double"></i> Asistencia confirmada por el profesor
                            </div>
                        <?php else: ?>
                            <button type="button" 
                                    class="btn-cancelar-reserva" 
                                    data-reserva-id="<?php echo $turno->id; ?>"
                                    data-clase="<?php echo s($turno->plan_nombre); ?>"
                                    data-fecha="<?php echo $diaNombre . ' ' . $fechaFormato; ?>"
                                    data-horario="<?php echo s(substr($turno->hora_inicio, 0, 5)) . ' - ' . s(substr($turno->hora_fin, 0, 5)) . ' hs'; ?>">
                                <i class="fa-solid fa-xmark"></i> Cancelar esta reserva
                            </button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Historial de Clases Anteriores o Canceladas -->
<div class="turnos-seccion">
    <h3 class="turnos-seccion__titulo">
        <i class="fa-solid fa-clock-rotate-left turnos-seccion__icono--gris"></i> Historial de Clases
    </h3>

    <?php if(empty($turnosPasados)): ?>
        <p class="turnos-seccion__vacio-texto">Aún no tenés historial de clases pasadas.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Disciplina</th>
                        <th>Profesor</th>
                        <th>Estado / Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($turnosPasados as $historial): 
                        $timestamp = strtotime($historial->fecha);
                        $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        $diaNombre = $diasSemana[(int)date('w', $timestamp)];
                        $fechaFormato = date('d/m/Y', $timestamp);
                        $esCancelada = $historial->estado === 'cancelada';
                    ?>
                        <tr>
                            <td><strong><?php echo $diaNombre; ?></strong> <?php echo $fechaFormato; ?></td>
                            <td><?php echo s(substr($historial->hora_inicio, 0, 5)); ?> - <?php echo s(substr($historial->hora_fin, 0, 5)); ?> hs</td>
                            <td>
                                <strong><?php echo s($historial->plan_nombre); ?></strong>
                                <?php if(!empty($historial->descripcion)): ?>
                                    <span class="subtexto-descripcion"><?php echo s($historial->descripcion); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo s($historial->entrenador); ?></td>
                            <td>
                                <?php if($esCancelada): ?>
                                    <span class="badge-asistencia badge-asistencia--cancelada">
                                        <i class="fa-solid fa-ban"></i> Cancelada
                                    </span>
                                <?php elseif((string)$historial->asistencia_presente === '1'): ?>
                                    <span class="badge-asistencia badge-asistencia--presente">
                                        <i class="fa-solid fa-circle-check"></i> Presente
                                    </span>
                                <?php elseif((string)$historial->asistencia_presente === '0'): ?>
                                    <span class="badge-asistencia badge-asistencia--ausente">
                                        <i class="fa-solid fa-circle-xmark"></i> Ausente
                                    </span>
                                <?php else: ?>
                                    <span class="badge-asistencia badge-asistencia--pendiente">
                                        <i class="fa-regular fa-clock"></i> Sin registrar
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

<!-- Modal de Confirmación de Cancelación de Turno -->
<div class="modal-overlay" id="modal_confirmar_cancelar" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-confirmar-cancelar">
        <div class="modal-confirmar-cancelar__icono">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-confirmar-cancelar__titulo">¿Cancelar este turno?</h3>
        <p class="modal-confirmar-cancelar__mensaje">
            Se liberará tu lugar en la clase para que otro alumno pueda reservarlo.
        </p>

        <div class="modal-confirmar-cancelar__info-clase">
            <div><i class="fa-solid fa-dumbbell"></i> <strong id="modal_cancelar_clase">-</strong></div>
            <div><i class="fa-regular fa-calendar"></i> <span id="modal_cancelar_fecha">-</span></div>
            <div><i class="fa-regular fa-clock"></i> <span id="modal_cancelar_hora">-</span></div>
        </div>

        <div class="modal-confirmar-cancelar__acciones">
            <button type="button" class="btn-mantener" id="btn_modal_cancelar_cerrar">
                Mantener mi turno
            </button>
            <button type="button" class="btn-confirmar-eliminar" id="btn_modal_cancelar_confirmar">
                <i class="fa-solid fa-trash-can"></i> Sí, cancelar
            </button>
        </div>
    </div>
</div>
