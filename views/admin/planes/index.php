<?php
/** @var array $planes */
/** @var mixed $resultado */
/** @var mixed $error */
?>

<h1>Planes</h1>

<?php
$mensaje = obtenerMensaje($resultado);
if($mensaje):
?>
    <div class="alerta exito"><?php echo s($mensaje); ?></div>
<?php endif; ?>

<?php if(($error ?? '') === 'plan_con_membresias'): ?>
    <div class="alerta error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>No se puede eliminar este plan porque tiene clientes o membresías asociadas en el historial. Podés desactivarlo desde "Editar" para que no esté disponible para nuevos alumnos.</span>
    </div>
<?php elseif(($error ?? '') === 'csrf'): ?>
    <div class="alerta error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Solicitud no válida o token de seguridad expirado. Por favor, intentá nuevamente.</span>
    </div>
<?php endif; ?>

<a class="boton" href="/admin/planes/crear">
    <i class="fa-solid fa-plus"></i>
    <span>Nuevo Plan</span>
</a>

<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Disciplina</th>
                <th>Precio</th>
                <th>Duración</th>
                <th>Clases</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($planes as $plan): ?>
                <tr>
                    <td>
                        <?php if($plan->imagen): ?>
                            <img class="tabla__thumb" src="/imagenes/<?php echo s($plan->imagen); ?>" alt="<?php echo s($plan->nombre); ?>">
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo s($plan->nombre); ?></strong></td>
                    <td>
                        <?php if(($plan->tipo_disciplina ?? 'musculacion') === 'crossfit'): ?>
                            <span class="badge badge--naranja"><i class="fa-solid fa-stopwatch"></i> Crossfit / WOD</span>
                        <?php elseif(($plan->tipo_disciplina ?? '') === 'otro'): ?>
                            <span class="badge badge--verde"><i class="fa-solid fa-water"></i> Otro / Pileta</span>
                        <?php else: ?>
                            <span class="badge badge--azul"><i class="fa-solid fa-dumbbell"></i> Musculación</span>
                        <?php endif; ?>
                    </td>
                    <td>$<?php echo number_format((float)$plan->precio, 0, ',', '.'); ?></td>
                    <td><?php echo s((string)$plan->duracion_dias); ?> días</td>
                    <td><?php echo empty($plan->cantidad_clases) ? 'Ilimitadas' : s($plan->cantidad_clases); ?></td>
                    <td><?php echo (int)$plan->activo ? 'Sí' : 'No'; ?></td>
                    <td class="tabla__acciones">
                        <a class="btn-micro btn-micro--editar" href="/admin/planes/actualizar?id=<?php echo $plan->id; ?>" title="Editar Plan">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form method="POST" action="/admin/planes/eliminar" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este plan?');">
                            <?php echo csrfCampo(); ?>
                            <input type="hidden" name="id" value="<?php echo $plan->id; ?>">
                            <button type="submit" class="btn-micro btn-micro--eliminar" title="Eliminar Plan">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
