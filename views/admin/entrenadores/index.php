<?php
/** @var array $entrenadores */
/** @var mixed $resultado */
$mensaje = $resultado ? obtenerMensaje((int)$resultado) : '';
$error = $_GET['error'] ?? null;
?>

<div class="entrenadores-header">
    <div>
        <h1>Gestión de Entrenadores</h1>
        <p>Administrá a los profesores del staff, sus datos de contacto y clases a cargo.</p>
    </div>
    <a href="/admin/entrenadores/crear" class="boton">
        <i class="fa-solid fa-user-plus"></i> Registrar Nuevo Entrenador
    </a>
</div>

<!-- Mensajes de feedback -->
<?php if($mensaje): ?>
    <div class="alerta exito">
        <i class="fa-solid fa-circle-check"></i>
        <span>Entrenador <?php echo s($mensaje); ?></span>
    </div>
<?php endif; ?>

<?php if($error === 'clases_asignadas'): ?>
    <div class="alerta error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>No podés eliminar este entrenador porque tiene clases asignadas en los horarios. Primero reasigná o eliminá sus clases.</span>
    </div>
<?php endif; ?>

<!-- Resumen y Buscador -->
<div class="entrenadores-toolbar">
    <div class="entrenadores-toolbar__buscador">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" 
               id="filtro_entrenadores" 
               placeholder="Buscar por nombre, email o teléfono..." 
               onkeyup="filtrarEntrenadores(this.value)">
    </div>

    <div class="entrenadores-toolbar__contador">
        Total de profesores: <strong><?php echo count($entrenadores); ?></strong>
    </div>
</div>

<?php if(empty($entrenadores)): ?>
    <div class="agenda-vacia-card">
        <i class="fa-solid fa-user-slash" style="color: #cbd5e1;"></i>
        <h3>No hay entrenadores registrados</h3>
        <p>Comenzá dando de alta al primer profesor del gimnasio.</p>
        <a href="/admin/entrenadores/crear" class="boton">
            <i class="fa-solid fa-user-plus"></i> Registrar Primer Entrenador
        </a>
    </div>
<?php else: ?>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th style="width: 6rem; text-align: center;">#</th>
                    <th>Entrenador</th>
                    <th>Contacto / Email</th>
                    <th>Teléfono</th>
                    <th style="text-align: center;">Clases Semanales</th>
                    <th style="text-align: center; width: 14rem;">Acciones</th>
                </tr>
            </thead>
            <tbody id="lista_entrenadores">
                <?php $i = 1; foreach($entrenadores as $entrenador): ?>
                    <tr class="fila-entrenador" 
                        data-nombre="<?php echo strtolower($entrenador->nombre . ' ' . $entrenador->apellido); ?>"
                        data-email="<?php echo strtolower($entrenador->email); ?>"
                        data-telefono="<?php echo strtolower($entrenador->telefono); ?>">
                        <td style="text-align: center; color: #94a3b8; font-weight: 700;">
                            <?php echo $i++; ?>
                        </td>
                        <td>
                            <div class="entrenador-info">
                                <div class="avatar-entrenador">
                                    <?php echo strtoupper(substr($entrenador->nombre, 0, 1) . substr($entrenador->apellido, 0, 1)); ?>
                                </div>
                                <div>
                                    <strong class="entrenador-info__nombre">
                                        <?php echo s($entrenador->nombre . ' ' . $entrenador->apellido); ?>
                                    </strong>
                                    <span class="badge-activo">
                                        <i class="fa-solid fa-circle"></i> Activo
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="entrenador-contacto">
                                <i class="fa-regular fa-envelope"></i>
                                <?php echo s($entrenador->email); ?>
                            </span>
                        </td>
                        <td>
                            <?php if(!empty($entrenador->telefono)): ?>
                                <?php $telLimpio = preg_replace('/[^0-9]/', '', $entrenador->telefono); ?>
                                <a href="https://wa.me/<?php echo $telLimpio; ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="btn-whatsapp" 
                                   title="Escribir por WhatsApp">
                                    <i class="fa-brands fa-whatsapp"></i> <?php echo s($entrenador->telefono); ?>
                                </a>
                            <?php else: ?>
                                <span style="color: #94a3b8;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php $totalClases = (int)($entrenador->total_clases ?? 0); ?>
                            <?php if($totalClases > 0): ?>
                                <div class="clases-col-wrapper">
                                    <span class="badge-clases-entrenador">
                                        <i class="fa-solid fa-clock"></i> <?php echo $totalClases; ?> <?php echo $totalClases === 1 ? 'clase' : 'clases'; ?>
                                    </span>
                                    <button type="button" 
                                            class="btn-ver-clases" 
                                            onclick="verClasesEntrenador(<?php echo $entrenador->id; ?>, '<?php echo s($entrenador->nombre . ' ' . $entrenador->apellido); ?>')"
                                            title="Ver cronograma semanal de <?php echo s($entrenador->nombre); ?>">
                                        <i class="fa-solid fa-eye"></i> Ver
                                    </button>
                                </div>
                                <?php if(!empty($entrenador->clases_nombres)): ?>
                                    <span class="disciplinas-entrenador">
                                        <?php echo s($entrenador->clases_nombres); ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 1.25rem;">Sin clases aún</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <div class="acciones-tabla">
                                <a href="/admin/entrenadores/actualizar?id=<?php echo $entrenador->id; ?>" 
                                   class="btn-micro btn-micro--editar" 
                                   title="Editar datos del entrenador">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                <form method="POST" 
                                      action="/admin/entrenadores/eliminar" 
                                      onsubmit="return confirm('¿Seguro que deseas eliminar al entrenador <?php echo s($entrenador->nombre . ' ' . $entrenador->apellido); ?>?');">
                                    <input type="hidden" name="id" value="<?php echo $entrenador->id; ?>">
                                    <button type="submit" 
                                            class="btn-micro btn-micro--eliminar" 
                                            title="Eliminar entrenador">
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
<?php endif; ?>

<!-- Modal de Clases Semanales del Entrenador -->
<div id="modal_clases" class="modal-overlay" onclick="cerrarModalAfuera(event)">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-header__info">
                <h3><i class="fa-solid fa-calendar-week"></i> Clases Semanales</h3>
                <p>Cronograma asignado para <strong id="modal_entrenador_nombre"></strong></p>
            </div>
            <button type="button" class="modal-header__cerrar" onclick="cerrarModalClases()" title="Cerrar modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body" id="modal_clases_body">
            <!-- Contenido dinámico inyectado por JS -->
        </div>

        <div class="modal-footer">
            <button type="button" class="boton-cerrar-modal" onclick="cerrarModalClases()">
                Cerrar
            </button>
        </div>
    </div>
</div>

