<?php
/** @var string $nombre */
/** @var array $planes */
/** @var int $planSeleccionado */
/** @var string $fechaSeleccionada */
/** @var string $tipoVista */
/** @var bool $tienePersonalizada */
/** @var \Model\Rutina|null $rutina */
/** @var array $bloques */
/** @var bool $esUltimaDisponible */

$esCrossfit = $rutina && stripos($rutina->plan_nombre, 'crossfit') !== false;

$nombresBloquesCrossfit = [
    'core' => '1° PARTE: CORE / ZONA MEDIA',
    'warmup' => '2° PARTE: WARMUP (CALENTAMIENTO)',
    'fuerza' => '3° PARTE: FUERZA / SKILL',
    'wod' => '4° PARTE: WOD (WORKOUT OF THE DAY)'
];
?>

<div class="entrenadores-header">
    <div>
        <h1>Mi Entrenamiento</h1>
        <p>Consultá la rutina diaria de tu plan, tus WODs y los ejercicios asignados por tus profesores.</p>
    </div>
</div>

<?php if(empty($planes) && !$tienePersonalizada): ?>
    <div class="banner-alerta-sin-membresia" style="margin-bottom: 2.5rem;">
        <div class="banner-alerta-sin-membresia__icono">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="banner-alerta-sin-membresia__cuerpo">
            <div class="banner-alerta-sin-membresia__header">
                <h3>No tenés membresías activas para ver rutinas</h3>
                <span class="badge badge--rojo"><i class="fa-solid fa-lock"></i> Requerido</span>
            </div>
            <p>Para poder visualizar los ejercicios y rutinas asignadas a tu entrenamiento, necesitás contar con un plan activo.</p>
            <div class="banner-alerta-sin-membresia__acciones">
                <a href="/cliente/planes" class="boton boton--primario">
                    <i class="fa-solid fa-cart-shopping"></i> Ver Planes y Membresías
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- BARRA DE FILTROS: FECHA Y DISCIPLINAS -->
<!-- ========================================== -->
<?php if(!empty($planes) || $tienePersonalizada): ?>
<div class="rutinas-toolbar">
    <div class="rutinas-toolbar__filtros">
        <?php if($tienePersonalizada): ?>
            <a href="/cliente/rutinas?tipo=personalizada" 
               class="filtro-chip <?php echo $tipoVista === 'personalizada' ? 'activo' : ''; ?>"
               style="<?php echo $tipoVista === 'personalizada' ? 'background: #0284c7; color: white;' : ''; ?>">
                <i class="fa-solid fa-user-check"></i> Mi Rutina Personalizada
            </a>
        <?php endif; ?>

        <?php foreach($planes as $p): 
            $esActivo = ($tipoVista !== 'personalizada' && (int)$planSeleccionado === (int)$p->id);
        ?>
            <a href="/cliente/rutinas?plan_id=<?php echo $p->id; ?>&fecha=<?php echo $fechaSeleccionada; ?>" 
               class="filtro-chip <?php echo $esActivo ? 'activo' : ''; ?>">
                <?php echo s($p->nombre); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if($tipoVista !== 'personalizada' && !empty($planSeleccionado)): ?>
        <div class="rutinas-toolbar__fecha">
            <i class="fa-solid fa-calendar-day" style="color: #149b2b;"></i>
            <label for="filtro_fecha_cliente" style="font-weight: 600; color: #475569; font-size: 1.35rem; margin: 0;">Fecha:</label>
            <input type="date" 
                   id="filtro_fecha_cliente" 
                   value="<?php echo s($fechaSeleccionada); ?>"
                   onchange="location.href='/cliente/rutinas?plan_id=<?php echo $planSeleccionado; ?>&fecha=' + this.value;">
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Aviso si se está mostrando la última rutina histórica disponible -->
<?php if($esUltimaDisponible && $rutina): ?>
    <div class="alerta" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 1rem; padding: 1.4rem 1.8rem; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 1rem;">
        <i class="fa-solid fa-circle-info" style="font-size: 1.8rem; color: #3b82f6;"></i>
        <span>No se encontró una rutina publicada para el <strong><?php echo date('d/m/Y', strtotime($fechaSeleccionada)); ?></strong>. Te mostramos el último entrenamiento registrado para <strong><?php echo s($rutina->plan_nombre); ?></strong> (del día <strong><?php echo date('d/m/Y', strtotime($rutina->fecha)); ?></strong>).</span>
    </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- PIZARRA DEL ENTRENAMIENTO -->
<!-- ========================================== -->
<?php if(!$rutina): ?>
    <div style="background: #ffffff; padding: 5rem 2rem; text-align: center; border-radius: 1.4rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 3rem;">
        <i class="fa-solid fa-dumbbell" style="font-size: 4.8rem; color: #cbd5e1; margin-bottom: 1.5rem;"></i>
        <?php if(empty($planes) && !$tienePersonalizada): ?>
            <h3 style="font-size: 2.2rem; color: #0f172a; margin-bottom: 0.8rem;">No tenés membresías activas</h3>
            <p style="color: #64748b; font-size: 1.5rem; max-width: 50rem; margin: 0 auto 2rem auto;">
                Actualmente no contás con una membresía activa para visualizar rutinas o entrenamientos. Podés consultar los planes disponibles y suscribirte para comenzar.
            </p>
            <a href="/cliente/planes" class="boton boton--primario" style="display: inline-block;">
                <i class="fa-solid fa-cart-shopping"></i> Ver Planes y Membresías
            </a>
        <?php else: ?>
            <h3 style="font-size: 2.2rem; color: #0f172a; margin-bottom: 0.8rem;">No hay entrenamientos cargados</h3>
            <p style="color: #64748b; font-size: 1.5rem; max-width: 50rem; margin: 0 auto 2rem auto;">
                Aún no se ha registrado una rutina para esta disciplina en la fecha seleccionada. Probá eligiendo otra fecha o consultá a tu entrenador.
            </p>
            <a href="/cliente/rutinas?plan_id=<?php echo $planSeleccionado; ?>&fecha=<?php echo date('Y-m-d'); ?>" class="boton" style="display: inline-block;">
                <i class="fa-solid fa-rotate-left"></i> Ver Rutina de Hoy
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
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

            <div style="display: flex; align-items: center; gap: 1.2rem;">
                <button type="button" 
                        class="btn-reiniciar-checks" 
                        onclick="reiniciarProgreso(<?php echo (int)$rutina->id; ?>)"
                        title="Reiniciar lista de completados">
                    <i class="fa-solid fa-rotate-right"></i> Reiniciar Progreso
                </button>
            </div>
        </div>

        <!-- Secciones / Bloques del Entrenamiento -->
        <?php if(empty($bloques) && empty($diasEstandar)): ?>
            <div class="modal-vacio">
                <i class="fa-solid fa-dumbbell"></i>
                <p>No se encontraron ejercicios detallados en este entrenamiento.</p>
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

                    <!-- Banner exclusivo de Crossfit con los Parámetros del WOD dentro de la tarjeta del WOD -->
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

                    <?php if(!empty($dataBloque['ejercicios'])): ?>
                        <div class="pizarra-wod__lista-ejercicios">
                            <?php foreach($dataBloque['ejercicios'] as $ej): ?>
                                <article class="pizarra-wod__item-ejercicio item-ejercicio-cliente" 
                                         id="item_ejercicio_<?php echo $ej->id; ?>"
                                         onclick="toggleCompletado(<?php echo (int)$rutina->id; ?>, <?php echo (int)$ej->id; ?>)">
                                    
                                    <!-- Fila superior: Checkbox + Imagen + Nombre del ejercicio + Botón Video -->
                                    <div class="item-ejercicio-cliente__cabecera">
                                        <!-- Checkbox interactivo -->
                                        <div class="check-circulo" id="check_circulo_<?php echo $ej->id; ?>">
                                            <i class="fa-solid fa-check"></i>
                                        </div>

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
                                               onclick="event.stopPropagation();"
                                               class="btn-video-tecnica"
                                               title="Ver video explicativo de la técnica">
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
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        <?php elseif($esMusculacion && empty($rutina->cliente_id)): ?>
            <!-- Rutina de Musculación por Días con Pestañas ♂ Hombres / ♀ Mujeres -->
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
                                    <article class="pizarra-wod__item-ejercicio item-ejercicio-cliente" 
                                             id="item_ejercicio_<?php echo $ej->id; ?>"
                                             onclick="toggleCompletado(<?php echo (int)$rutina->id; ?>, <?php echo (int)$ej->id; ?>)">
                                        
                                        <!-- Fila superior: Checkbox + Imagen + Nombre del ejercicio + Botón Video -->
                                        <div class="item-ejercicio-cliente__cabecera">
                                            <div class="check-circulo" id="check_circulo_<?php echo $ej->id; ?>">
                                                <i class="fa-solid fa-check"></i>
                                            </div>

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
                                                   onclick="event.stopPropagation();"
                                                   class="btn-video-tecnica"
                                                   title="Ver video explicativo de la técnica">
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
                                    <article class="pizarra-wod__item-ejercicio item-ejercicio-cliente" 
                                             id="item_ejercicio_<?php echo $ej->id; ?>"
                                             onclick="toggleCompletado(<?php echo (int)$rutina->id; ?>, <?php echo (int)$ej->id; ?>)">
                                        
                                        <!-- Fila superior: Checkbox + Imagen + Nombre del ejercicio + Botón Video -->
                                        <div class="item-ejercicio-cliente__cabecera">
                                            <div class="check-circulo" id="check_circulo_<?php echo $ej->id; ?>">
                                                <i class="fa-solid fa-check"></i>
                                            </div>

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
                                                   onclick="event.stopPropagation();"
                                                   class="btn-video-tecnica"
                                                   title="Ver video explicativo de la técnica">
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
            <!-- Rutina Estándar / Funcional por Días (unificada para hombres y mujeres con checklist) -->
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
                            <article class="pizarra-wod__item-ejercicio item-ejercicio-cliente" 
                                     id="item_ejercicio_<?php echo $ej->id; ?>"
                                     onclick="toggleCompletado(<?php echo (int)$rutina->id; ?>, <?php echo (int)$ej->id; ?>)">
                                
                                <div class="item-ejercicio-cliente__cabecera">
                                    <div class="check-circulo" id="check_circulo_<?php echo $ej->id; ?>">
                                        <i class="fa-solid fa-check"></i>
                                    </div>

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
                                           onclick="event.stopPropagation();"
                                           class="btn-video-tecnica"
                                           title="Ver video explicativo de la técnica">
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
<?php endif; ?>

<script>
// Sistema de Checklist Interactivo con persistencia local
const rutinaIdActual = <?php echo $rutina ? (int)$rutina->id : 0; ?>;

function cambiarPestanaDetalle(btn, idDia, genero) {
    const parentTabs = btn.parentElement;
    parentTabs.querySelectorAll('.btn-pizarra-genero').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const seccion = btn.closest('.pizarra-wod__seccion');
    const panelH = seccion.querySelector('.panel-genero-pizarra--hombres');
    const panelM = seccion.querySelector('.panel-genero-pizarra--mujeres');

    if(genero === 'hombres') {
        if(panelH) panelH.style.display = 'block';
        if(panelM) panelM.style.display = 'none';
    } else {
        if(panelH) panelH.style.display = 'none';
        if(panelM) panelM.style.display = 'block';
    }
}

function getCheckStorageKey(rutinaId, ejId) {
    return `gym_rutina_${rutinaId}_ej_${ejId}`;
}

function toggleCompletado(rutinaId, ejId) {
    const key = getCheckStorageKey(rutinaId, ejId);
    const estaCompletado = localStorage.getItem(key) === 'true';
    const nuevoEstado = !estaCompletado;

    if(nuevoEstado) {
        localStorage.setItem(key, 'true');
    } else {
        localStorage.removeItem(key);
    }

    aplicarEstadoUI(ejId, nuevoEstado);
}

function aplicarEstadoUI(ejId, completado) {
    const item = document.getElementById(`item_ejercicio_${ejId}`);
    const check = document.getElementById(`check_circulo_${ejId}`);
    if(!item || !check) return;

    if(completado) {
        item.classList.add('item-ejercicio-cliente--completado');
        check.classList.add('check-circulo--activo');
    } else {
        item.classList.remove('item-ejercicio-cliente--completado');
        check.classList.remove('check-circulo--activo');
    }
}

function cargarProgresoGuardado() {
    if(!rutinaIdActual) return;
    const items = document.querySelectorAll('.item-ejercicio-cliente');
    items.forEach(item => {
        const idStr = item.id.replace('item_ejercicio_', '');
        const ejId = parseInt(idStr, 10);
        if(ejId) {
            const key = getCheckStorageKey(rutinaIdActual, ejId);
            const completado = localStorage.getItem(key) === 'true';
            aplicarEstadoUI(ejId, completado);
        }
    });
}

function reiniciarProgreso(rutinaId) {
    if(!confirm('¿Deseás reiniciar la lista de ejercicios completados de este entrenamiento?')) return;
    const items = document.querySelectorAll('.item-ejercicio-cliente');
    items.forEach(item => {
        const idStr = item.id.replace('item_ejercicio_', '');
        const ejId = parseInt(idStr, 10);
        if(ejId) {
            const key = getCheckStorageKey(rutinaId, ejId);
            localStorage.removeItem(key);
            aplicarEstadoUI(ejId, false);
        }
    });
}

document.addEventListener('DOMContentLoaded', cargarProgresoGuardado);
</script>
