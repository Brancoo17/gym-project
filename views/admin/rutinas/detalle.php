<?php
/** @var \Model\Rutina $rutina */
/** @var array $bloques */
/** @var int $totalEjercicios */

$esCrossfit = stripos($rutina->plan_nombre, 'crossfit') !== false;

$nombresBloquesCrossfit = [
    'core' => '1° PARTE: CORE / ZONA MEDIA',
    'warmup' => '2° PARTE: WARMUP (CALENTAMIENTO)',
    'fuerza' => '3° PARTE: FUERZA / SKILL',
    'wod' => '4° PARTE: WOD (WORKOUT OF THE DAY)'
];
?>

<div class="entrenadores-header">
    <div>
        <a href="/admin/rutinas" class="boton boton--secundario" style="margin-bottom: 1rem;">
            <i class="fa-solid fa-arrow-left"></i> Volver a Rutinas
        </a>
        <h1>Pizarra de Entrenamiento</h1>
        <p>Cronograma técnico y desglose de ejercicios asignados.</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="/admin/rutinas/actualizar?id=<?php echo $rutina->id; ?>" class="boton">
            <i class="fa-solid fa-pen-to-square"></i> Editar Rutina
        </a>
    </div>
</div>

<div class="pizarra-wod">
    <div class="pizarra-wod__header">
        <div>
            <h1>
                <i class="fa-solid fa-fire-flame-curved"></i> 
                <?php echo s($rutina->nombre); ?>
            </h1>
            <p>
                Disciplina: <strong style="color: #ffffff;"><?php echo s($rutina->plan_nombre); ?></strong> &bull; 
                Fecha: <strong style="color: #ffffff;"><?php echo date('d/m/Y', strtotime($rutina->fecha)); ?></strong> &bull;
                Profesor: <strong style="color: #ffffff;"><?php echo s($rutina->entrenador_nombre); ?></strong>
            </p>
        </div>

        <div>
            <?php if(!empty($rutina->cliente_nombre)): ?>
                <span class="card-rutina__tag" style="font-size: 1.35rem; padding: 0.6rem 1.4rem; background: #1e293b; color: #38bdf8;">
                    <i class="fa-solid fa-user"></i> Alumno: <?php echo s($rutina->cliente_nombre); ?>
                </span>
            <?php else: ?>
                <span class="card-rutina__tag" style="font-size: 1.35rem; padding: 0.6rem 1.4rem; background: #1e293b; color: #4ade80;">
                    <i class="fa-solid fa-users"></i> Rutina Grupal de la Clase
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Secciones / Bloques del Entrenamiento -->
    <?php if(empty($bloques) && empty($diasEstandar)): ?>
        <div class="modal-vacio">
            <i class="fa-solid fa-dumbbell"></i>
            <p>No se encontraron ejercicios asignados en este entrenamiento.</p>
        </div>
    <?php elseif($esCrossfit): ?>
        <?php foreach($bloques as $claveBloque => $dataBloque): 
            $tituloBloque = $nombresBloquesCrossfit[$claveBloque] ?? strtoupper($claveBloque);
            $esBloqueWod = ($claveBloque === 'wod');
        ?>
            <section class="pizarra-wod__seccion <?php echo $esBloqueWod ? 'pizarra-wod__seccion--wod' : ''; ?>">
                <div class="pizarra-wod__seccion-titulo <?php echo $esBloqueWod ? 'pizarra-wod__seccion-titulo--wod' : ''; ?>">
                    <span>
                        <?php if($esBloqueWod): ?>
                            <i class="fa-solid fa-stopwatch" style="color: #149b2b; margin-right: 0.6rem;"></i>
                        <?php endif; ?>
                        <?php echo s($tituloBloque); ?>
                    </span>

                    <?php if(!empty($dataBloque['rondas'])): ?>
                        <span class="badge-rondas">
                            <i class="fa-solid fa-rotate-right"></i> <?php echo (int)$dataBloque['rondas']; ?> Rondas
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Banner exclusivo de Crossfit con los Parámetros del WOD dentro de la tarjeta de ejercicios del WOD -->
                <?php if($esBloqueWod && !empty($rutina->wod_formato)): ?>
                    <div class="pizarra-wod__wod-banner">
                        <div class="pizarra-wod__wod-banner-info">
                            <h3>
                                <i class="fa-solid fa-stopwatch"></i>
                                <?php echo s(\Model\Rutina::FORMATOS_WOD[$rutina->wod_formato] ?? strtoupper($rutina->wod_formato)); ?>
                            </h3>
                            <?php if(!empty($rutina->wod_descripcion)): ?>
                                <p><?php echo s($rutina->wod_descripcion); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($rutina->wod_tiempo)): ?>
                            <div class="pizarra-wod__wod-banner-tiempo">
                                <?php echo s($rutina->wod_tiempo); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="pizarra-wod__lista-ejercicios">
                    <?php foreach($dataBloque['ejercicios'] as $ej): ?>
                        <article class="pizarra-wod__item-ejercicio">
                            <!-- Fila superior: Imagen + Nombre del ejercicio + Botón Video -->
                            <div class="item-ejercicio-cliente__cabecera">
                                <?php if(!empty($ej->ejercicio_imagen)): ?>
                                    <img src="/imagenes/<?php echo s($ej->ejercicio_imagen); ?>" 
                                         alt="<?php echo s($ej->ejercicio_nombre); ?>" 
                                         class="pizarra-wod__item-ejercicio-img">
                                <?php else: ?>
                                    <div class="pizarra-wod__item-ejercicio-img">
                                        <i class="fa-solid fa-dumbbell"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="item-ejercicio-cliente__titulo-col">
                                    <strong class="item-ejercicio-cliente__nombre"><?php echo s($ej->ejercicio_nombre); ?></strong>
                                    <?php if(!empty($ej->grupo_muscular)): ?>
                                        <span class="item-ejercicio-cliente__grupo"><?php echo s($ej->grupo_muscular); ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if(!empty($ej->video_url)): ?>
                                    <a href="<?php echo s($ej->video_url); ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="btn-video-tecnica" 
                                       title="Ver demostración en video">
                                        <i class="fa-brands fa-youtube"></i>
                                        <span>Video</span>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Fila inferior: Métricas (Series, Reps, Rx y Notas) -->
                            <div class="item-ejercicio-cliente__metricas">
                                <?php if(!empty($ej->series) || !empty($ej->reps)): ?>
                                    <span class="metrica-badge metrica-badge--series">
                                        <i class="fa-solid fa-dumbbell"></i>
                                        <?php if(!empty($ej->series) && !empty($ej->reps)): ?>
                                            <strong><?php echo (int)$ej->series; ?></strong> series &times; <strong><?php echo (int)$ej->reps; ?></strong> reps
                                        <?php elseif(!empty($ej->reps)): ?>
                                            <strong><?php echo (int)$ej->reps; ?></strong> reps
                                        <?php elseif(!empty($ej->series)): ?>
                                            <strong><?php echo (int)$ej->series; ?></strong> series
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <?php if(!empty($ej->peso_hombres) || !empty($ej->peso_mujeres)): ?>
                                    <span class="metrica-badge metrica-badge--rx">
                                        <i class="fa-solid fa-scale-balanced"></i> Rx: 
                                        <?php if(!empty($rutina->cliente_id)): ?>
                                            <?php echo s($ej->peso_hombres ?: $ej->peso_mujeres); ?>
                                        <?php else: ?>
                                            <?php if(!empty($ej->peso_hombres)): ?> ♂ <?php echo s($ej->peso_hombres); ?> <?php endif; ?>
                                            <?php if(!empty($ej->peso_mujeres)): ?> ♀ <?php echo s($ej->peso_mujeres); ?> <?php endif; ?>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <?php if(!empty($ej->notas)): ?>
                                    <span class="metrica-badge metrica-badge--notas">
                                        <i class="fa-solid fa-circle-info"></i> <?php echo s($ej->notas); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php elseif($esMusculacion && empty($rutina->cliente_id)): ?>
        <!-- Rutina de Musculación por Días diferenciada por Género -->
        <?php foreach($diasEstandar as $diaNum => $dataDia): 
            $cantH = count($dataDia['hombres']);
            $cantM = count($dataDia['mujeres']);
            $mostrarHombresPrimero = ($cantH > 0 || $cantM === 0);
        ?>
            <section class="pizarra-wod__seccion">
                <div class="pizarra-wod__seccion-titulo">
                    <span>
                        <i class="fa-solid fa-calendar-day" style="color: #149b2b; margin-right: 0.6rem;"></i>
                        DÍA <?php echo $diaNum; ?> DE ENTRENAMIENTO
                    </span>
                </div>

                <!-- Pestañas ♂ Hombres / ♀ Mujeres dentro de cada Día -->
                <div class="pizarra-wod__genero-tabs">
                    <button type="button" 
                            class="btn-pizarra-genero btn-pizarra-genero--hombres <?php echo $mostrarHombresPrimero ? 'active' : ''; ?>" 
                            onclick="cambiarPestanaDetalle(this, 'dia_<?php echo $diaNum; ?>', 'hombres')">
                        <i class="fa-solid fa-mars"></i> 
                        <span>Rutina Hombres</span>
                        <span class="badge-count"><?php echo $cantH; ?> ej</span>
                    </button>
                    <button type="button" 
                            class="btn-pizarra-genero btn-pizarra-genero--mujeres <?php echo !$mostrarHombresPrimero ? 'active' : ''; ?>" 
                            onclick="cambiarPestanaDetalle(this, 'dia_<?php echo $diaNum; ?>', 'mujeres')">
                        <i class="fa-solid fa-venus"></i> 
                        <span>Rutina Mujeres</span>
                        <span class="badge-count"><?php echo $cantM; ?> ej</span>
                    </button>
                </div>

                <!-- Panel Hombres -->
                <div class="panel-genero-pizarra panel-genero-pizarra--hombres" 
                     id="panel_dia_<?php echo $diaNum; ?>_hombres"
                     style="<?php echo $mostrarHombresPrimero ? '' : 'display: none;'; ?>">
                    <?php if(empty($dataDia['hombres'])): ?>
                        <p style="color: #94a3b8; font-style: italic; padding: 2rem; text-align: center;">No se definieron ejercicios de hombres para este día.</p>
                    <?php else: ?>
                        <div class="pizarra-wod__lista-ejercicios">
                            <?php foreach($dataDia['hombres'] as $ej): ?>
                                <article class="pizarra-wod__item-ejercicio">
                                    <div class="item-ejercicio-cliente__cabecera">
                                        <?php if(!empty($ej->ejercicio_imagen)): ?>
                                            <img src="/imagenes/<?php echo s($ej->ejercicio_imagen); ?>" 
                                                 alt="<?php echo s($ej->ejercicio_nombre); ?>" 
                                                 class="pizarra-wod__item-ejercicio-img">
                                        <?php else: ?>
                                            <div class="pizarra-wod__item-ejercicio-img">
                                                <i class="fa-solid fa-dumbbell"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div class="item-ejercicio-cliente__titulo-col">
                                            <strong class="item-ejercicio-cliente__nombre"><?php echo s($ej->ejercicio_nombre); ?></strong>
                                            <?php if(!empty($ej->grupo_muscular)): ?>
                                                <span class="item-ejercicio-cliente__grupo"><?php echo s($ej->grupo_muscular); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if(!empty($ej->video_url)): ?>
                                            <a href="<?php echo s($ej->video_url); ?>" 
                                               target="_blank" 
                                               rel="noopener noreferrer" 
                                               class="btn-video-tecnica" 
                                               title="Ver demostración en video">
                                                <i class="fa-brands fa-youtube"></i>
                                                <span>Video</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <div class="item-ejercicio-cliente__metricas">
                                        <?php if(!empty($ej->series) || !empty($ej->reps)): ?>
                                            <span class="metrica-badge metrica-badge--series">
                                                <i class="fa-solid fa-dumbbell"></i>
                                                <?php if(!empty($ej->series) && !empty($ej->reps)): ?>
                                                    <strong><?php echo (int)$ej->series; ?></strong> series &times; <strong><?php echo (int)$ej->reps; ?></strong> reps
                                                <?php elseif(!empty($ej->reps)): ?>
                                                    <strong><?php echo (int)$ej->reps; ?></strong> reps
                                                <?php elseif(!empty($ej->series)): ?>
                                                    <strong><?php echo (int)$ej->series; ?></strong> series
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if(!empty($ej->notas)): ?>
                                            <span class="metrica-badge metrica-badge--notas">
                                                <i class="fa-solid fa-circle-info"></i> <?php echo s($ej->notas); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Panel Mujeres -->
                <div class="panel-genero-pizarra panel-genero-pizarra--mujeres" 
                     id="panel_dia_<?php echo $diaNum; ?>_mujeres"
                     style="<?php echo !$mostrarHombresPrimero ? '' : 'display: none;'; ?>">
                    <?php if(empty($dataDia['mujeres'])): ?>
                        <p style="color: #94a3b8; font-style: italic; padding: 2rem; text-align: center;">No se definieron ejercicios de mujeres para este día.</p>
                    <?php else: ?>
                        <div class="pizarra-wod__lista-ejercicios">
                            <?php foreach($dataDia['mujeres'] as $ej): ?>
                                <article class="pizarra-wod__item-ejercicio">
                                    <div class="item-ejercicio-cliente__cabecera">
                                        <?php if(!empty($ej->ejercicio_imagen)): ?>
                                            <img src="/imagenes/<?php echo s($ej->ejercicio_imagen); ?>" 
                                                 alt="<?php echo s($ej->ejercicio_nombre); ?>" 
                                                 class="pizarra-wod__item-ejercicio-img">
                                        <?php else: ?>
                                            <div class="pizarra-wod__item-ejercicio-img">
                                                <i class="fa-solid fa-dumbbell"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div class="item-ejercicio-cliente__titulo-col">
                                            <strong class="item-ejercicio-cliente__nombre"><?php echo s($ej->ejercicio_nombre); ?></strong>
                                            <?php if(!empty($ej->grupo_muscular)): ?>
                                                <span class="item-ejercicio-cliente__grupo"><?php echo s($ej->grupo_muscular); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if(!empty($ej->video_url)): ?>
                                            <a href="<?php echo s($ej->video_url); ?>" 
                                               target="_blank" 
                                               rel="noopener noreferrer" 
                                               class="btn-video-tecnica" 
                                               title="Ver demostración en video">
                                                <i class="fa-brands fa-youtube"></i>
                                                <span>Video</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <div class="item-ejercicio-cliente__metricas">
                                        <?php if(!empty($ej->series) || !empty($ej->reps)): ?>
                                            <span class="metrica-badge metrica-badge--series">
                                                <i class="fa-solid fa-dumbbell"></i>
                                                <?php if(!empty($ej->series) && !empty($ej->reps)): ?>
                                                    <strong><?php echo (int)$ej->series; ?></strong> series &times; <strong><?php echo (int)$ej->reps; ?></strong> reps
                                                <?php elseif(!empty($ej->reps)): ?>
                                                    <strong><?php echo (int)$ej->reps; ?></strong> reps
                                                <?php elseif(!empty($ej->series)): ?>
                                                    <strong><?php echo (int)$ej->series; ?></strong> series
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if(!empty($ej->notas)): ?>
                                            <span class="metrica-badge metrica-badge--notas">
                                                <i class="fa-solid fa-circle-info"></i> <?php echo s($ej->notas); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Rutina Estándar / Funcional por Días (unificada para hombres y mujeres) -->
        <?php foreach($diasEstandar as $diaNum => $dataDia): ?>
            <section class="pizarra-wod__seccion">
                <div class="pizarra-wod__seccion-titulo">
                    <span>
                        <i class="fa-solid fa-calendar-day" style="color: #149b2b; margin-right: 0.6rem;"></i>
                        DÍA <?php echo $diaNum; ?> DE ENTRENAMIENTO
                    </span>

                    <?php if(!empty($dataDia['rondas'])): ?>
                        <span class="badge-rondas">
                            <i class="fa-solid fa-rotate-right"></i> <?php echo (int)$dataDia['rondas']; ?> Rondas / Vueltas
                        </span>
                    <?php endif; ?>
                </div>

                <div class="pizarra-wod__lista-ejercicios">
                    <?php foreach($dataDia['ejercicios'] ?? [] as $ej): ?>
                        <article class="pizarra-wod__item-ejercicio">
                            <div class="item-ejercicio-cliente__cabecera">
                                <?php if(!empty($ej->ejercicio_imagen)): ?>
                                    <img src="/imagenes/<?php echo s($ej->ejercicio_imagen); ?>" 
                                         alt="<?php echo s($ej->ejercicio_nombre); ?>" 
                                         class="pizarra-wod__item-ejercicio-img">
                                <?php else: ?>
                                    <div class="pizarra-wod__item-ejercicio-img">
                                        <i class="fa-solid fa-dumbbell"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="item-ejercicio-cliente__titulo-col">
                                    <strong class="item-ejercicio-cliente__nombre"><?php echo s($ej->ejercicio_nombre); ?></strong>
                                    <?php if(!empty($ej->grupo_muscular)): ?>
                                        <span class="item-ejercicio-cliente__grupo"><?php echo s($ej->grupo_muscular); ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if(!empty($ej->video_url)): ?>
                                    <a href="<?php echo s($ej->video_url); ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="btn-video-tecnica" 
                                       title="Ver demostración en video">
                                        <i class="fa-brands fa-youtube"></i>
                                        <span>Video</span>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="item-ejercicio-cliente__metricas">
                                <?php if(!empty($ej->series) || !empty($ej->reps)): ?>
                                    <span class="metrica-badge metrica-badge--series">
                                        <i class="fa-solid fa-dumbbell"></i>
                                        <?php if(!empty($ej->series) && !empty($ej->reps)): ?>
                                            <strong><?php echo (int)$ej->series; ?></strong> series &times; <strong><?php echo (int)$ej->reps; ?></strong> reps
                                        <?php elseif(!empty($ej->reps)): ?>
                                            <strong><?php echo (int)$ej->reps; ?></strong> reps
                                        <?php elseif(!empty($ej->series)): ?>
                                            <strong><?php echo (int)$ej->series; ?></strong> series
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <?php if(!empty($ej->notas)): ?>
                                    <span class="metrica-badge metrica-badge--notas">
                                        <i class="fa-solid fa-circle-info"></i> <?php echo s($ej->notas); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

