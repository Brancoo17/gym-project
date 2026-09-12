<?php
/** @var array $horarios */
/** @var array $planes */
/** @var bool $esAdmin */
$esAdmin = $esAdmin ?? false;

// Días de la semana (Lunes a Sábado, sin Domingo)
$diasNombres = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
];

// Agrupar horarios por día
$horariosPorDia = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => []];
foreach($horarios as $h) {
    $dia = (int)$h->dia_semana;
    if(isset($horariosPorDia[$dia])) {
        $horariosPorDia[$dia][] = $h;
    }
}

// Determinar el día inicial activo (el primer día que tenga clases, o Lunes por defecto)
$diaInicial = 1;
foreach($diasNombres as $numDia => $nombreDia) {
    if(!empty($horariosPorDia[$numDia])) {
        $diaInicial = $numDia;
        break;
    }
}
?>

<div class="cronograma-componente">
    <!-- Pestañas de Días (Lunes a Sábado) -->
    <div class="cronograma-tabs-dias">
        <?php foreach($diasNombres as $numDia => $nombreDia): 
            $cantClases = count($horariosPorDia[$numDia]);
        ?>
            <button type="button" 
                    class="tab-dia-btn <?php echo $numDia === $diaInicial ? 'activo' : ''; ?>" 
                    data-dia="<?php echo $numDia; ?>">
                <span class="tab-dia-btn__nombre"><?php echo $nombreDia; ?></span>
                <span class="tab-dia-btn__badge"><?php echo $cantClases; ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Barra de Filtros por Disciplina -->
    <div class="cronograma-filtros">
        <span class="cronograma-filtros__label"><i class="fa-solid fa-filter"></i> Disciplina:</span>
        <div class="cronograma-filtros__botones">
            <button type="button" class="filtro-btn activo" data-plan="todos">Todas</button>
            <?php 
                $planesUnicos = [];
                foreach($horarios as $h) {
                    $planesUnicos[$h->plan_id] = $h->plan_nombre;
                }
                foreach($planesUnicos as $planId => $planNombre): 
            ?>
                <button type="button" class="filtro-btn" data-plan="<?php echo $planId; ?>">
                    <?php echo s($planNombre); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Contenido de Clases por Día -->
    <div class="cronograma-paneles">
        <?php foreach($diasNombres as $numDia => $nombreDia): 
            $clasesDelDia = $horariosPorDia[$numDia];
        ?>
            <div class="panel-dia <?php echo $numDia === $diaInicial ? 'panel-dia--activo' : ''; ?>" 
                 data-panel-dia="<?php echo $numDia; ?>">
                
                <div class="panel-dia__header">
                    <h3>Horarios del <?php echo $nombreDia; ?></h3>
                    <span class="panel-dia__resumen">
                        <?php echo count($clasesDelDia); ?> <?php echo count($clasesDelDia) === 1 ? 'turno disponible' : 'turnos disponibles'; ?>
                    </span>
                </div>

                <?php if(empty($clasesDelDia)): ?>
                    <div class="panel-dia__vacio">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <h4>No hay clases programadas para este día</h4>
                        <p>Podés consultar los otros días de la semana en las pestañas de arriba.</p>
                    </div>
                <?php else: ?>
                    <div class="grid-tarjetas-horarios">
                        <?php foreach($clasesDelDia as $clase): ?>
                            <article class="tarjeta-horario" data-plan-id="<?php echo $clase->plan_id; ?>">
                                <div class="tarjeta-horario__header">
                                    <div class="tarjeta-horario__hora">
                                        <i class="fa-regular fa-clock"></i>
                                        <span><?php echo s(substr($clase->hora_inicio, 0, 5)); ?> - <?php echo s(substr($clase->hora_fin, 0, 5)); ?> hs</span>
                                    </div>

                                    <?php if($esAdmin): ?>
                                        <div class="tarjeta-horario__acciones">
                                            <a href="/admin/horarios/actualizar?id=<?php echo $clase->id; ?>" 
                                               class="btn-icon btn-icon--editar" 
                                               title="Editar este turno">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form method="POST" action="/admin/horarios/eliminar" onsubmit="return confirm('¿Eliminar el turno de <?php echo s($clase->plan_nombre); ?> a las <?php echo s(substr($clase->hora_inicio, 0, 5)); ?>?');">
                                                <input type="hidden" name="id" value="<?php echo $clase->id; ?>">
                                                <button type="submit" class="btn-icon btn-icon--eliminar" title="Eliminar este turno">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="tarjeta-horario__cuerpo">
                                    <h4 class="tarjeta-horario__titulo"><?php echo s($clase->plan_nombre); ?></h4>
                                    
                                    <?php if(!empty($clase->descripcion)): ?>
                                        <div class="tarjeta-horario__descripcion">
                                            <i class="fa-solid fa-circle-info"></i>
                                            <span><?php echo s($clase->descripcion); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="tarjeta-horario__detalles">
                                        <div class="detalle-item">
                                            <i class="fa-solid fa-user-tie"></i>
                                            <span>Prof. <strong><?php echo s($clase->entrenador); ?></strong></span>
                                        </div>
                                        <div class="detalle-item">
                                            <i class="fa-solid fa-users"></i>
                                            <span>Cupo: <strong><?php echo (!is_null($clase->cupo) && $clase->cupo !== '') ? s((string)$clase->cupo) . ' lugares' : 'Ilimitado'; ?></strong></span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
