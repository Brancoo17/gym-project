<?php
/** @var array $clientes */
/** @var array $kpis */
/** @var mixed $resultado */
/** @var string $filtroEstado */
/** @var string $filtroMembresia */

$clientes = $clientes ?? [];
$kpis = $kpis ?? [
    'total' => 0,
    'confirmados' => 0,
    'pendientes' => 0,
    'conMembresia' => 0,
    'totalAsistencias' => 0
];

$mensajes = [
    1 => 'Cliente registrado exitosamente',
    2 => 'Cliente actualizado correctamente',
    3 => 'Cliente eliminado correctamente',
    4 => 'Estado de cuenta del alumno actualizado correctamente'
];
$mensajeFeedback = $mensajes[(int)$resultado] ?? null;
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Gestión de Clientes y Alumnos</h1>
        <p>Administrá la base de clientes, altas en mostrador, estado de confirmación y seguimiento de entrenamientos.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/admin/usuarios/crear" class="boton">
            <i class="fa-solid fa-user-plus"></i> Registrar Nuevo Alumno
        </a>
    </div>
</div>

<!-- Mensaje de feedback -->
<?php if($mensajeFeedback): ?>
    <div class="alerta exito">
        <i class="fa-solid fa-circle-check"></i>
        <span><?php echo s($mensajeFeedback); ?></span>
    </div>
<?php endif; ?>

<!-- Barra de Métricas Rápidas de Clientes -->
<div class="agenda-metricas-grid">
    <div class="metrica-card metrica-card--borde-azul">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span class="metrica-card__label">Total de Alumnos</span>
            <strong class="metrica-card__valor"><?php echo $kpis['total']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-verde">
        <div class="metrica-card__icono metrica-card__icono--verde">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Cuentas Activas</span>
            <strong class="metrica-card__valor metrica-card__valor--verde"><?php echo $kpis['confirmados']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-amarillo">
        <div class="metrica-card__icono metrica-card__icono--amarillo">
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Pendientes de Confirmar</span>
            <strong class="metrica-card__valor metrica-card__valor--amarillo"><?php echo $kpis['pendientes']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-naranja">
        <div class="metrica-card__icono metrica-card__icono--naranja">
            <i class="fa-solid fa-id-card"></i>
        </div>
        <div>
            <span class="metrica-card__label">Membresías Activas</span>
            <strong class="metrica-card__valor"><?php echo $kpis['conMembresia']; ?></strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-morado">
        <div class="metrica-card__icono metrica-card__icono--morado">
            <i class="fa-solid fa-dumbbell"></i>
        </div>
        <div>
            <span class="metrica-card__label">Asistencias Totales</span>
            <strong class="metrica-card__valor metrica-card__valor--morado"><?php echo $kpis['totalAsistencias']; ?></strong>
        </div>
    </div>
</div>

<!-- Toolbar de Búsqueda y Filtros Rápidos -->
<div class="ejercicios-toolbar">
    <div class="ejercicios-toolbar__top">
        <div class="ejercicios-toolbar__buscador">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" 
                   id="filtro_busqueda_clientes" 
                   placeholder="Buscar alumno por nombre, DNI, email o teléfono..." 
                   onkeyup="filtrarTablaClientes()">
        </div>

        <div class="ejercicios-toolbar__contador">
            Alumnos mostrados: <strong id="contador_visibles"><?php echo count($clientes); ?></strong>
        </div>
    </div>

    <!-- Chips de Filtrado -->
    <div class="ejercicios-toolbar__chips">
        <button type="button" 
                class="filtro-chip activo" 
                data-filtro="todos" 
                onclick="seleccionarFiltroEstado(this, 'todos')">
            <i class="fa-solid fa-users"></i> Todos (<?php echo count($clientes); ?>)
        </button>

        <button type="button" 
                class="filtro-chip" 
                data-filtro="confirmados" 
                onclick="seleccionarFiltroEstado(this, 'confirmados')">
            <i class="fa-solid fa-check"></i> Activos / Confirmados (<?php echo $kpis['confirmados']; ?>)
        </button>

        <button type="button" 
                class="filtro-chip" 
                data-filtro="pendientes" 
                onclick="seleccionarFiltroEstado(this, 'pendientes')">
            <i class="fa-solid fa-clock"></i> Pendientes de Confirmar (<?php echo $kpis['pendientes']; ?>)
        </button>

        <button type="button" 
                class="filtro-chip" 
                data-filtro="con_membresia" 
                onclick="seleccionarFiltroEstado(this, 'con_membresia')">
            <i class="fa-solid fa-id-card"></i> Con Membresía (<?php echo $kpis['conMembresia']; ?>)
        </button>
    </div>
</div>

<?php if(empty($clientes)): ?>
    <div class="agenda-vacia-card">
        <i class="fa-solid fa-users-slash icono-muted"></i>
        <h3>No hay alumnos registrados</h3>
        <p>Comenzá dando de alta al primer cliente desde el mostrador.</p>
        <a href="/admin/usuarios/crear" class="boton">
            <i class="fa-solid fa-user-plus"></i> Registrar Primer Alumno
        </a>
    </div>
<?php else: ?>
    <div class="tabla-contenedor">
        <table class="tabla" id="tabla_clientes">
            <thead>
                <tr>
                    <th class="tabla__th--id">ID</th>
                    <th>Alumno</th>
                    <th>DNI</th>
                    <th>Contacto</th>
                    <th class="tabla__th--centro">Estado Cuenta</th>
                    <th>Membresía / Plan</th>
                    <th class="tabla__th--centro">Asistencias</th>
                    <th class="tabla__th--acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($clientes as $cliente): 
                    $esConfirmado = ((int)$cliente['confirmado'] === 1);
                    $tieneMembresia = !empty($cliente['planes_activos']);
                    $nombreCompleto = $cliente['nombre'] . ' ' . $cliente['apellido'];
                    $iniciales = strtoupper(substr($cliente['nombre'], 0, 1) . substr($cliente['apellido'], 0, 1));
                    $telLimpio = preg_replace('/[^0-9]/', '', $cliente['telefono'] ?? '');
                ?>
                    <tr class="fila-cliente" 
                        data-nombre="<?php echo strtolower($nombreCompleto); ?>"
                        data-dni="<?php echo strtolower($cliente['dni'] ?? ''); ?>"
                        data-email="<?php echo strtolower($cliente['email']); ?>"
                        data-telefono="<?php echo strtolower($cliente['telefono'] ?? ''); ?>"
                        data-confirmado="<?php echo $esConfirmado ? 'confirmados' : 'pendientes'; ?>"
                        data-membresia="<?php echo $tieneMembresia ? 'con_membresia' : 'sin_membresia'; ?>">
                        
                        <td class="tabla__td--id">
                            #<?php echo $cliente['id']; ?>
                        </td>

                        <td>
                            <div class="tabla-usuario">
                                <div class="tabla-usuario__avatar">
                                    <?php echo $iniciales; ?>
                                </div>
                                <div class="tabla-usuario__info">
                                    <strong><?php echo s($nombreCompleto); ?></strong>
                                    <?php if(!empty($cliente['ultima_reserva'])): ?>
                                        <span>Último turno: <?php echo date('d/m/Y', strtotime($cliente['ultima_reserva'])); ?></span>
                                    <?php else: ?>
                                        <span class="sin-turnos">Sin turnos reservados</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <td>
                            <?php if(!empty($cliente['dni'])): ?>
                                <span class="tabla-dni">
                                    <i class="fa-solid fa-id-card"></i> <?php echo s($cliente['dni']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge-estado badge-estado--sin-dni">Sin DNI</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="tabla-contacto">
                                <a href="mailto:<?php echo s($cliente['email']); ?>" class="tabla-contacto__email">
                                    <i class="fa-regular fa-envelope"></i><?php echo s($cliente['email']); ?>
                                </a>
                                <?php if(!empty($cliente['telefono'])): ?>
                                    <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($cliente['nombre']); ?>%2C%20te%20escribimos%20desde%20el%20gimnasio." 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="tabla-contacto__whatsapp">
                                        <i class="fa-brands fa-whatsapp"></i> <?php echo s($cliente['telefono']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="tabla-contacto__sin-telefono">Sin teléfono</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="tabla__td--centro">
                            <?php if($esConfirmado): ?>
                                <span class="badge-estado badge-estado--activa">
                                    <i class="fa-solid fa-circle-check"></i> Activa
                                </span>
                            <?php else: ?>
                                <div class="estado-pendiente-wrapper">
                                    <span class="badge-estado badge-estado--pendiente">
                                        <i class="fa-solid fa-clock"></i> Pendiente
                                    </span>
                                    <form method="POST" action="/admin/usuarios/toggle-confirmar">
                                        <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
                                        <button type="submit" 
                                                class="btn-activar-cuenta"
                                                title="Confirmar cuenta manualmente (el alumno podrá iniciar sesión de inmediato)">
                                            <i class="fa-solid fa-check"></i> Activar
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td>
                        <?php if(!empty($cliente['planes_activos'])): ?>
                            <?php 
                                $planesArray = explode(', ', $cliente['planes_activos']);
                                foreach($planesArray as $p):
                            ?>
                                <span class="badge badge--celeste" style="margin-bottom: 0.5rem; display: inline-block;">
                                    <i class="fa-solid fa-dumbbell"></i> <?php echo s($p); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="badge badge--rojo">Sin membresía</span>
                        <?php endif; ?>
                        <div class="acciones">
                            <a href="/admin/usuarios/membresia-manual?id=<?php echo $cliente['id']; ?>" class="boton-accion boton-accion--verde" title="Activar/Renovar Membresía Manualmente">
                                <i class="fa-solid fa-money-bill"></i>
                            </a>
                        </div>
                    </td>

                        <td class="tabla__td--centro">
                            <span class="badge-asistencias">
                                <i class="fa-solid fa-user-check"></i> <?php echo (int)$cliente['total_asistencias']; ?>
                            </span>
                            <span class="subtexto-reservas">
                                (<?php echo (int)$cliente['total_reservas']; ?> reservas)
                            </span>
                        </td>

                        <td class="tabla__td--centro">
                            <div class="acciones-tabla acciones-tabla--centro">
                                <?php if(!empty($cliente['telefono'])): ?>
                                    <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($cliente['nombre']); ?>%2C%20te%20contactamos%20desde%20el%20gimnasio." 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="btn-micro btn-micro--whatsapp" 
                                       title="Escribir por WhatsApp">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="/admin/usuarios/actualizar?id=<?php echo $cliente['id']; ?>" 
                                   class="btn-micro btn-micro--editar" 
                                   title="Editar datos del alumno">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                <a href="/admin/usuarios/detalle?id=<?php echo $cliente['id']; ?>" 
                                   class="btn-micro btn-micro--ficha" 
                                   title="Ver ficha y entrenamientos del alumno">
                                    <i class="fa-solid fa-address-card"></i>
                                </a>

                                <form method="POST" action="/admin/usuarios/eliminar" onsubmit="return confirm('¿Eliminar al alumno <?php echo s($nombreCompleto); ?>?');">
                                    <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
                                    <button type="submit" class="btn-micro btn-micro--eliminar" title="Eliminar alumno">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mensaje cuando la búsqueda no coincide -->
    <div id="sin_resultados_clientes" class="agenda-vacia-card usuarios-sin-resultados">
        <i class="fa-solid fa-magnifying-glass icono-muted"></i>
        <h3>No se encontraron alumnos</h3>
        <p>Intentá con otro nombre, email o cambiando el filtro de estado.</p>
    </div>
<?php endif; ?>

