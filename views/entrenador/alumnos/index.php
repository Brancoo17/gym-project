<?php
/** @var array $kpis */
/** @var array $alumnos */

$alumnos = $alumnos ?? [];
$kpis = $kpis ?? [
    'totalAlumnosClases' => 0,
    'totalAsistenciasDadas' => 0,
    'totalRutinasAsignadas' => 0
];
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Alumnos y Alumnas</h1>
        <p>Consultá el seguimiento, asistencias y rutinas de los alumnos que entrenan en tus clases.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/entrenador" class="boton-accion">
            <i class="fa-solid fa-arrow-left"></i> Volver al Panel
        </a>
    </div>
</div>

<!-- Barra de Métricas Rápidas del Entrenador -->
<div class="agenda-metricas-grid">
    <div class="metrica-card metrica-card--borde-azul">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span class="metrica-card__label">Alumnos en tus Clases</span>
            <strong class="metrica-card__valor"><?php echo $kpis['totalAlumnosClases']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-verde">
        <div class="metrica-card__icono metrica-card__icono--verde">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Asistencias Registradas</span>
            <strong class="metrica-card__valor metrica-card__valor--verde"><?php echo $kpis['totalAsistenciasDadas']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-naranja">
        <div class="metrica-card__icono metrica-card__icono--naranja">
            <i class="fa-solid fa-dumbbell"></i>
        </div>
        <div>
            <span class="metrica-card__label">Rutinas Asignadas</span>
            <strong class="metrica-card__valor"><?php echo $kpis['totalRutinasAsignadas']; ?></strong>
        </div>
    </div>
</div>

<!-- Toolbar de Búsqueda y Filtros Rápidos -->
<div class="ejercicios-toolbar">
    <div class="ejercicios-toolbar__top">
        <div class="ejercicios-toolbar__buscador">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" 
                   id="filtro_busqueda_alumnos" 
                   placeholder="Buscar por nombre, email o teléfono..." 
                   onkeyup="filtrarTablaAlumnos()">
        </div>

        <div class="ejercicios-toolbar__contador">
            Alumnos mostrados: <strong id="contador_alumnos_visibles"><?php echo count($alumnos); ?></strong>
        </div>
    </div>

    <!-- Chips de Filtrado -->
    <div class="ejercicios-toolbar__chips">
        <button type="button" 
                class="filtro-chip activo" 
                data-filtro="todos" 
                onclick="seleccionarFiltroAlumnos(this, 'todos')">
            <i class="fa-solid fa-users"></i> Todos (<?php echo count($alumnos); ?>)
        </button>

        <button type="button" 
                class="filtro-chip" 
                data-filtro="en_mis_clases" 
                onclick="seleccionarFiltroAlumnos(this, 'en_mis_clases')">
            <i class="fa-solid fa-calendar-check"></i> Asisten a mis clases (<?php echo $kpis['totalAlumnosClases']; ?>)
        </button>

        <button type="button" 
                class="filtro-chip" 
                data-filtro="con_rutina" 
                onclick="seleccionarFiltroAlumnos(this, 'con_rutina')">
            <i class="fa-solid fa-dumbbell"></i> Con Rutina Asignada (<?php echo $kpis['totalRutinasAsignadas']; ?>)
        </button>
    </div>
</div>

<?php if(empty($alumnos)): ?>
    <div class="agenda-vacia-card">
        <i class="fa-solid fa-users-slash icono-muted"></i>
        <h3>No hay alumnos registrados</h3>
        <p>Aún no hay clientes registrados en el gimnasio.</p>
    </div>
<?php else: ?>
    <div class="tabla-contenedor">
        <table class="tabla" id="tabla_alumnos_entrenador">
            <thead>
                <tr>
                    <th class="tabla__th--id">#</th>
                    <th>Alumno</th>
                    <th>Contacto</th>
                    <th class="tabla__th--centro">Clases Conmigo</th>
                    <th class="tabla__th--centro">Rutinas Asignadas</th>
                    <th>Plan / Membresía</th>
                    <th class="tabla__th--acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($alumnos as $alumno): 
                    $nombreCompleto = $alumno['nombre'] . ' ' . $alumno['apellido'];
                    $iniciales = strtoupper(substr($alumno['nombre'], 0, 1) . substr($alumno['apellido'], 0, 1));
                    $telLimpio = preg_replace('/[^0-9]/', '', $alumno['telefono'] ?? '');
                    $tieneClases = (int)$alumno['reservas_con_profesor'] > 0;
                    $tieneRutinas = (int)$alumno['rutinas_con_profesor'] > 0;
                ?>
                    <tr class="fila-alumno-entrenador"
                        data-nombre="<?php echo strtolower($nombreCompleto); ?>"
                        data-dni="<?php echo strtolower($alumno['dni'] ?? ''); ?>"
                        data-email="<?php echo strtolower($alumno['email']); ?>"
                        data-telefono="<?php echo strtolower($alumno['telefono'] ?? ''); ?>"
                        data-en-mis-clases="<?php echo $tieneClases ? '1' : '0'; ?>"
                        data-con-rutina="<?php echo $tieneRutinas ? '1' : '0'; ?>">

                        <td class="tabla__td--id">
                            #<?php echo $alumno['id']; ?>
                        </td>

                        <td>
                            <div class="tabla-usuario">
                                <div class="tabla-usuario__avatar">
                                    <?php echo $iniciales; ?>
                                </div>
                                <div class="tabla-usuario__info">
                                    <strong><?php echo s($nombreCompleto); ?></strong>
                                    <?php if(!empty($alumno['dni'])): ?>
                                        <span class="subtexto-dni" style="display: block; font-size: 1.2rem; color: #64748b; margin-top: 0.1rem;"><i class="fa-solid fa-id-card"></i> DNI: <?php echo s($alumno['dni']); ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($alumno['ultima_clase_profesor'])): ?>
                                        <span>Última clase: <?php echo date('d/m/Y', strtotime($alumno['ultima_clase_profesor'])); ?></span>
                                    <?php elseif($tieneClases): ?>
                                        <span>Inscripto en tus clases</span>
                                    <?php else: ?>
                                        <span class="sin-turnos">Sin clases con este profesor</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="tabla-contacto">
                                <a href="mailto:<?php echo s($alumno['email']); ?>" class="tabla-contacto__email">
                                    <i class="fa-regular fa-envelope"></i> <?php echo s($alumno['email']); ?>
                                </a>
                                <?php if(!empty($alumno['telefono'])): ?>
                                    <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($alumno['nombre']); ?>%2C%20te%20escribo%20desde%20el%20gimnasio." 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="tabla-contacto__whatsapp">
                                        <i class="fa-brands fa-whatsapp"></i> <?php echo s($alumno['telefono']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="tabla-contacto__sin-telefono">Sin teléfono</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="tabla__td--centro">
                            <?php if($tieneClases): ?>
                                <span class="badge-asistencias">
                                    <i class="fa-solid fa-user-check"></i> <?php echo (int)$alumno['asistencias_con_profesor']; ?> presentes
                                </span>
                                <span class="subtexto-reservas">
                                    (<?php echo (int)$alumno['reservas_con_profesor']; ?> turnos)
                                </span>
                            <?php else: ?>
                                <span class="subtexto-reservas">-</span>
                            <?php endif; ?>
                        </td>

                        <td class="tabla__td--centro">
                            <?php if($tieneRutinas): ?>
                                <span class="badge-membresia badge-membresia--activa">
                                    <i class="fa-solid fa-dumbbell"></i> <?php echo (int)$alumno['rutinas_con_profesor']; ?> asignada<?php echo (int)$alumno['rutinas_con_profesor'] > 1 ? 's' : ''; ?>
                                </span>
                            <?php else: ?>
                                <span class="badge-membresia badge-membresia--sin-plan">
                                    Ninguna
                                </span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if(!empty($alumno['planes_activos'])): ?>
                                <?php 
                                    $planesArray = explode(', ', $alumno['planes_activos']);
                                    foreach($planesArray as $p):
                                ?>
                                    <span class="badge badge--celeste" style="margin-bottom: 0.5rem; display: inline-block;">
                                        <i class="fa-solid fa-dumbbell"></i> <?php echo s($p); ?>
                                    </span><br>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="badge-membresia badge-membresia--sin-plan">
                                    Sin plan activo
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="tabla__td--centro">
                            <div class="acciones-tabla acciones-tabla--centro">
                                <?php if(!empty($alumno['telefono'])): ?>
                                    <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($alumno['nombre']); ?>%2C%20te%20escribo%20desde%20el%20gimnasio." 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="btn-micro btn-micro--whatsapp" 
                                       title="Escribir por WhatsApp">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="/entrenador/alumnos/detalle?id=<?php echo $alumno['id']; ?>" 
                                   class="btn-micro btn-micro--ficha" 
                                   title="Ver ficha y seguimiento del alumno">
                                    <i class="fa-solid fa-address-card"></i>
                                </a>

                                <a href="/admin/rutinas/crear?cliente_id=<?php echo $alumno['id']; ?>" 
                                   class="btn-micro btn-micro--editar" 
                                   title="Diseñar rutina personalizada para este alumno">
                                    <i class="fa-solid fa-dumbbell"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mensaje cuando la búsqueda no coincide -->
    <div id="sin_resultados_alumnos_entrenador" class="agenda-vacia-card usuarios-sin-resultados">
        <i class="fa-solid fa-magnifying-glass icono-muted"></i>
        <h3>No se encontraron alumnos</h3>
        <p>Intentá con otro nombre, email o cambiando el filtro seleccionado.</p>
    </div>
<?php endif; ?>

