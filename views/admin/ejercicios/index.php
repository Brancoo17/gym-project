<?php
/** @var array $ejercicios */
/** @var array $grupos */
/** @var mixed $resultado */
/** @var mixed $error */
/** @var bool $puedeGestionar */
$puedeGestionar = $puedeGestionar ?? false;
$mensaje = $resultado ? obtenerMensaje((int)$resultado) : '';
?>

<div class="ejercicios-header">
    <div>
        <h1>Banco de Ejercicios</h1>
        <p>Catálogo completo de ejercicios categorizados por grupo muscular, técnica y videos demostrativos.</p>
    </div>
    <?php if($puedeGestionar): ?>
        <a href="/admin/ejercicios/crear" class="boton">
            <i class="fa-solid fa-plus"></i> Registrar Nuevo Ejercicio
        </a>
    <?php endif; ?>
</div>

<!-- Mensajes de feedback -->
<?php if($mensaje): ?>
    <div class="alerta exito">
        <i class="fa-solid fa-circle-check"></i>
        <span>Ejercicio <?php echo s($mensaje); ?></span>
    </div>
<?php endif; ?>

<?php if($error === 'ejercicio_en_rutina'): ?>
    <div class="alerta error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>No podés eliminar este ejercicio porque ya se encuentra asignado a una o más rutinas de alumnos.</span>
    </div>
<?php endif; ?>

<!-- Barra de búsqueda y filtros rápidos por grupo muscular -->
<div class="ejercicios-toolbar">
    <div class="ejercicios-toolbar__top">
        <div class="ejercicios-toolbar__buscador">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" 
                   id="filtro_ejercicios" 
                   placeholder="Buscar ejercicio por nombre o técnica..." 
                   onkeyup="filtrarCatalogo()">
        </div>

        <div class="ejercicios-toolbar__contador">
            Ejercicios disponibles: <strong id="contador_visibles"><?php echo count($ejercicios); ?></strong>
        </div>
    </div>

    <!-- Chips de filtrado por grupo muscular -->
    <div class="ejercicios-toolbar__chips">
        <button type="button" 
                class="filtro-chip activo" 
                data-grupo="" 
                onclick="seleccionarGrupo(this, '')">
            <i class="fa-solid fa-layer-group"></i> Todos (<?php echo count($ejercicios); ?>)
        </button>
        <?php foreach($grupos as $g): ?>
            <?php 
                $cantidadPorGrupo = count(array_filter($ejercicios, fn($e) => $e->grupo_muscular === $g)); 
            ?>
            <button type="button" 
                    class="filtro-chip" 
                    data-grupo="<?php echo s($g); ?>" 
                    onclick="seleccionarGrupo(this, '<?php echo s($g); ?>')">
                <?php echo s($g); ?> (<?php echo $cantidadPorGrupo; ?>)
            </button>
        <?php endforeach; ?>
    </div>
</div>

<?php if(empty($ejercicios)): ?>
    <div class="agenda-vacia-card">
        <i class="fa-solid fa-dumbbell"></i>
        <h3>No hay ejercicios en el catálogo</h3>
        <p><?php echo $puedeGestionar ? 'Comenzá registrando el primer ejercicio para que los entrenadores puedan armar rutinas.' : 'Aún no se han cargado ejercicios en el catálogo.'; ?></p>
        <?php if($puedeGestionar): ?>
            <a href="/admin/ejercicios/crear" class="boton">
                <i class="fa-solid fa-plus"></i> Registrar Primer Ejercicio
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- Grilla visual de tarjetas de ejercicios -->
    <div class="grid-ejercicios" id="contenedor_ejercicios">
        <?php foreach($ejercicios as $ejercicio): ?>
            <article class="card-ejercicio" 
                     data-nombre="<?php echo strtolower($ejercicio->nombre); ?>"
                     data-musculo="<?php echo s($ejercicio->grupo_muscular); ?>"
                     data-descripcion="<?php echo strtolower($ejercicio->descripcion); ?>">
                
                <div class="card-ejercicio__imagen-contenedor">
                    <?php if(!empty($ejercicio->imagen)): ?>
                        <img src="/imagenes/<?php echo s($ejercicio->imagen); ?>" 
                             alt="<?php echo s($ejercicio->nombre); ?>" 
                             loading="lazy">
                    <?php else: ?>
                        <div class="card-ejercicio__imagen-contenedor--placeholder">
                            <i class="fa-solid fa-dumbbell"></i>
                            <span>Sin imagen</span>
                        </div>
                    <?php endif; ?>

                    <span class="card-ejercicio__badge-musculo">
                        <i class="fa-solid fa-fire-flame-curved"></i> <?php echo s($ejercicio->grupo_muscular); ?>
                    </span>

                    <?php if(!empty($ejercicio->video_url)): ?>
                        <a href="<?php echo s($ejercicio->video_url); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="card-ejercicio__badge-video" 
                           title="Ver demostración en video">
                            <i class="fa-brands fa-youtube"></i> Video
                        </a>
                    <?php endif; ?>
                </div>

                <div class="card-ejercicio__cuerpo">
                    <h3><?php echo s($ejercicio->nombre); ?></h3>
                    <p><?php echo s($ejercicio->descripcion); ?></p>
                </div>

                <div class="card-ejercicio__footer">
                    <span class="card-ejercicio__grupo-texto">
                        <?php echo s($ejercicio->grupo_muscular); ?>
                    </span>

                    <?php if($puedeGestionar): ?>
                        <div class="acciones-tabla">
                            <a href="/admin/ejercicios/actualizar?id=<?php echo $ejercicio->id; ?>" 
                                class="btn-micro btn-micro--editar" 
                                title="Editar ejercicio">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <form method="POST" 
                                  action="/admin/ejercicios/eliminar" 
                                  onsubmit="return confirm('¿Seguro que deseas eliminar el ejercicio <?php echo s($ejercicio->nombre); ?>?');">
                                <input type="hidden" name="id" value="<?php echo $ejercicio->id; ?>">
                                <button type="submit" 
                                        class="btn-micro btn-micro--eliminar" 
                                        title="Eliminar ejercicio">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <?php if(!empty($ejercicio->video_url)): ?>
                            <a href="<?php echo s($ejercicio->video_url); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="card-ejercicio__link-video">
                                <i class="fa-brands fa-youtube"></i> Ver técnica
                            </a>
                        <?php else: ?>
                            <span class="card-ejercicio__tag-tecnica">
                                <i class="fa-solid fa-circle-check"></i> Técnica
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Mensaje cuando no hay resultados en la búsqueda -->
    <div id="sin_resultados" class="agenda-vacia-card agenda-vacia-card--busqueda">
        <i class="fa-solid fa-magnifying-glass"></i>
        <h3>No se encontraron ejercicios</h3>
        <p>Intentá con otro nombre o seleccioná otro grupo muscular.</p>
    </div>
<?php endif; ?>

