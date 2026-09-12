<?php
/** @var string $nombre */
/** @var string $periodo */
/** @var string $tituloPeriodo */
/** @var array $kpis */
/** @var array $porDisciplina */
/** @var array $rankingAlumnos */
/** @var array $diasMap */
/** @var array $topHorarios */

$periodo = $periodo ?? 'mes_actual';
$tituloPeriodo = $tituloPeriodo ?? 'Mes Actual';
$kpis = $kpis ?? [
    'totalReservas' => 0,
    'totalActivas' => 0,
    'totalCanceladas' => 0,
    'totalPresentes' => 0,
    'totalAusentes' => 0,
    'totalEvaluadas' => 0,
    'tasaAsistencia' => 0,
    'tasaAusentismo' => 0,
    'tasaCancelacion' => 0
];
$porDisciplina = $porDisciplina ?? [];
$rankingAlumnos = $rankingAlumnos ?? [];
$diasMap = $diasMap ?? [];
$topHorarios = $topHorarios ?? [];
?>

<div class="reportes-header">
    <div class="reportes-header__info">
        <div class="reportes-header__titulo">
            <h1>Métricas y Reportes de Asistencia</h1>
            <span class="badge-periodo">
                <i class="fa-solid fa-calendar-check"></i> <?php echo s($tituloPeriodo); ?>
            </span>
        </div>
        <p>Análisis estadístico de presentismo, ausentismo, demanda por disciplina y fidelidad de alumnos.</p>
    </div>

    <div class="reportes-header__acciones no-print">
        <button type="button" onclick="window.print()" class="boton-accion">
            <i class="fa-solid fa-print"></i> Imprimir Informe
        </button>
        <a href="/admin/reservas" class="boton">
            <i class="fa-solid fa-clipboard-user"></i> Ir a la Agenda
        </a>
    </div>
</div>

<!-- Selector de Período (Pestañas Rápidas) -->
<div class="reportes-filtros no-print">
    <a href="/admin/reportes?periodo=mes_actual" 
       class="btn-filtro-periodo <?php echo $periodo === 'mes_actual' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-regular fa-calendar"></i> Mes Actual
    </a>
    <a href="/admin/reportes?periodo=mes_anterior" 
       class="btn-filtro-periodo <?php echo $periodo === 'mes_anterior' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-solid fa-clock-rotate-left"></i> Mes Anterior
    </a>
    <a href="/admin/reportes?periodo=ultimos_30" 
       class="btn-filtro-periodo <?php echo $periodo === 'ultimos_30' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-solid fa-hourglass-half"></i> Últimos 30 Días
    </a>
    <a href="/admin/reportes?periodo=anio_actual" 
       class="btn-filtro-periodo <?php echo $periodo === 'anio_actual' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-solid fa-calendar-days"></i> Todo el <?php echo date('Y'); ?>
    </a>
    <a href="/admin/reportes?periodo=historico" 
       class="btn-filtro-periodo <?php echo $periodo === 'historico' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-solid fa-database"></i> Histórico Completo
    </a>
</div>

<!-- KPIs Principales de Rendimiento -->
<div class="agenda-metricas-grid">
    <div class="metrica-card metrica-card--borde-verde">
        <div class="metrica-card__icono metrica-card__icono--verde">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div>
            <span class="metrica-card__label">Tasa de Asistencia</span>
            <strong class="metrica-card__valor metrica-card__valor--verde"><?php echo $kpis['tasaAsistencia']; ?>%</strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-azul">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Presentes Confirmados</span>
            <strong class="metrica-card__valor"><?php echo $kpis['totalPresentes']; ?> <span class="metrica-card__subvalor">(asistieron)</span></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-rojo">
        <div class="metrica-card__icono metrica-card__icono--rojo">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
        <div>
            <span class="metrica-card__label">Ausencias Registradas</span>
            <strong class="metrica-card__valor metrica-card__valor--rojo"><?php echo $kpis['totalAusentes']; ?> <span class="metrica-card__subvalor">(<?php echo $kpis['tasaAusentismo']; ?>%)</span></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-amarillo">
        <div class="metrica-card__icono metrica-card__icono--naranja">
            <i class="fa-solid fa-ticket"></i>
        </div>
        <div>
            <span class="metrica-card__label">Total Reservas Período</span>
            <strong class="metrica-card__valor"><?php echo $kpis['totalReservas']; ?> <span class="metrica-card__subvalor">(<?php echo $kpis['totalCanceladas']; ?> canceladas)</span></strong>
        </div>
    </div>
</div>

<!-- Layout de Distribución: Disciplinas y Días -->
<div class="reportes-grid-dos">
    
    <!-- Asistencia y Demanda por Disciplina -->
    <div class="reportes-card reportes-card--grid">
        <div class="reportes-card__header">
            <h3>
                <i class="fa-solid fa-dumbbell"></i> Presentismo por Disciplina
            </h3>
            <span class="reportes-card__subtitulo">Período seleccionado</span>
        </div>

        <?php if(empty($porDisciplina)): ?>
            <p class="centrado texto-gris">No hay registros de reservas en este período.</p>
        <?php else: ?>
            <div class="disciplinas-lista">
                <?php foreach($porDisciplina as $disc): 
                    $totalD = (int)$disc['total_reservas'];
                    $presD = (int)$disc['presentes'];
                    $ausD = (int)$disc['ausentes'];
                    $cancD = (int)$disc['canceladas'];
                    $evalD = $presD + $ausD;
                    $pctD = ($evalD > 0) ? round(($presD / $evalD) * 100) : 0;
                ?>
                    <div class="disciplina-item">
                        <div class="disciplina-item__top">
                            <strong><?php echo s($disc['nombre']); ?></strong>
                            <span><?php echo $pctD; ?>% asistencia</span>
                        </div>

                        <!-- Barra de progreso visual -->
                        <div class="barra-progreso">
                            <div class="barra-progreso__fill barra-progreso__fill--presente" style="width: <?php echo $pctD; ?>%;" title="Presentes: <?php echo $presD; ?>"></div>
                            <?php if($evalD > 0 && $ausD > 0): 
                                $pctAusD = round(($ausD / $evalD) * 100);
                            ?>
                                <div class="barra-progreso__fill barra-progreso__fill--ausente" style="width: <?php echo $pctAusD; ?>%;" title="Ausentes: <?php echo $ausD; ?>"></div>
                            <?php endif; ?>
                        </div>

                        <div class="disciplina-item__stats">
                            <span><i class="fa-solid fa-check"></i> <strong><?php echo $presD; ?></strong> presentes</span>
                            <span><i class="fa-solid fa-xmark"></i> <strong><?php echo $ausD; ?></strong> ausentes</span>
                            <span><i class="fa-solid fa-ban"></i> <strong><?php echo $cancD; ?></strong> canceladas</span>
                            <span>Total: <strong><?php echo $totalD; ?></strong></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Concurrencia por Día de la Semana -->
    <div class="reportes-card reportes-card--grid">
        <div class="reportes-card__header">
            <h3>
                <i class="fa-solid fa-calendar-week"></i> Concurrencia por Día
            </h3>
            <span class="reportes-card__subtitulo">Lunes a Sábados</span>
        </div>

        <?php 
        $maxReservasDia = 1;
        foreach($diasMap as $dInfo) {
            if($dInfo['reservas'] > $maxReservasDia) $maxReservasDia = $dInfo['reservas'];
        }
        ?>

        <div class="dias-concurrencia-lista">
            <?php foreach($diasMap as $diaNum => $dInfo): 
                $pctBarra = round(($dInfo['reservas'] / $maxReservasDia) * 100);
            ?>
                <div class="dia-concurrencia-item">
                    <div class="dia-concurrencia-item__top">
                        <span><?php echo $dInfo['nombre']; ?></span>
                        <span>
                            <strong><?php echo $dInfo['presentes']; ?></strong> presentes / <strong><?php echo $dInfo['reservas']; ?></strong> reservas
                        </span>
                    </div>

                    <div class="barra-progreso barra-progreso--alta">
                        <div class="barra-progreso__fill barra-progreso__fill--gradiente" style="width: <?php echo max($pctBarra, 4); ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Ranking de Alumnos Más Constantes (Top 10) -->
<div class="reportes-card">
    <div class="reportes-card__header">
        <div>
            <h3>
                <i class="fa-solid fa-trophy"></i> Ranking de Alumnos Más Constantes
            </h3>
            <p>Alumnos con mayor asistencia confirmada a sus entrenamientos en este período.</p>
        </div>
        <span class="ranking-badge-fidelidad">
            Top 10 Fidelidad
        </span>
    </div>

    <?php if(empty($rankingAlumnos)): ?>
        <p class="centrado texto-gris">Aún no hay asistencias confirmadas registradas en este período.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th class="col-centrada col-estrecha">Puesto</th>
                        <th>Alumno</th>
                        <th>Email de Contacto</th>
                        <th>WhatsApp</th>
                        <th class="col-centrada">Asistencias</th>
                        <th class="col-centrada">Reservas</th>
                        <th class="col-centrada">Efectividad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $puesto = 1;
                    foreach($rankingAlumnos as $alumno): 
                        $totalA = (int)$alumno['total_reservas'];
                        $presA = (int)$alumno['presentes'];
                        $pctEfectivo = ($totalA > 0) ? round(($presA / $totalA) * 100) : 0;
                    ?>
                        <tr>
                            <td class="col-centrada">
                                <?php if($puesto === 1): ?>
                                    <span class="ranking-puesto ranking-puesto--1">
                                        <i class="fa-solid fa-crown"></i>
                                    </span>
                                <?php elseif($puesto === 2): ?>
                                    <span class="ranking-puesto ranking-puesto--2">
                                        2°
                                    </span>
                                <?php elseif($puesto === 3): ?>
                                    <span class="ranking-puesto ranking-puesto--3">
                                        3°
                                    </span>
                                <?php else: ?>
                                    <span class="ranking-puesto--otro"><?php echo $puesto; ?>°</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong>
                                    <?php echo s($alumno['nombre'] . ' ' . $alumno['apellido']); ?>
                                </strong>
                            </td>

                            <td class="texto-gris">
                                <?php echo s($alumno['email']); ?>
                            </td>

                            <td>
                                <?php if(!empty($alumno['telefono'])): 
                                    $telLimpio = preg_replace('/[^0-9]/', '', $alumno['telefono']);
                                ?>
                                    <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($alumno['nombre']); ?>%2C%20te%20escribimos%20desde%20el%20gimnasio%20para%20felicitarte%20por%20tu%20constancia%20en%20los%20entrenamientos!" 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="tabla-contacto__whatsapp">
                                        <i class="fa-brands fa-whatsapp"></i> <?php echo s($alumno['telefono']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="tabla-contacto__sin-telefono">Sin teléfono</span>
                                <?php endif; ?>
                            </td>

                            <td class="col-centrada">
                                <span class="badge-asistencias">
                                    <?php echo $presA; ?>
                                </span>
                            </td>

                            <td class="col-centrada texto-gris font-negrita">
                                <?php echo $totalA; ?>
                            </td>

                            <td class="col-centrada">
                                <span class="badge-efectividad <?php echo $pctEfectivo >= 80 ? 'texto-verde' : ($pctEfectivo >= 50 ? 'texto-azul' : 'texto-amarillo'); ?>">
                                    <?php echo $pctEfectivo; ?>%
                                </span>
                            </td>
                        </tr>
                    <?php 
                        $puesto++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Top Turnos Más Concurridos -->
<?php if(!empty($topHorarios)): ?>
    <div class="reportes-card">
        <div class="reportes-card__header">
            <h3>
                <i class="fa-solid fa-fire"></i> Turnos con Mayor Demanda y Concurrencia
            </h3>
        </div>

        <div class="top-turnos-grid">
            <?php 
            $diasNombres = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            foreach($topHorarios as $th): 
                $diaTurno = $diasNombres[(int)$th['dia_semana']] ?? 'Día';
            ?>
                <div class="card-top-turno">
                    <div class="card-top-turno__header">
                        <span class="card-top-turno__hora">
                            <i class="fa-regular fa-clock"></i> <?php echo substr($th['hora_inicio'], 0, 5); ?> - <?php echo substr($th['hora_fin'], 0, 5); ?> hs
                        </span>
                        <span class="card-top-turno__dia">
                            <?php echo $diaTurno; ?>
                        </span>
                    </div>
                    <h4><?php echo s($th['plan_nombre']); ?></h4>
                    <p>Prof. <?php echo s($th['entrenador']); ?></p>
                    <div class="card-top-turno__asistentes">
                        <i class="fa-solid fa-check"></i> <?php echo $th['presentes']; ?> asistencias confirmadas (<?php echo $th['total_reservas']; ?> reservas)
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

