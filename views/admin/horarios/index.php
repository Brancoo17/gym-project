<?php
/** @var array $horarios */
/** @var array $planes */
/** @var mixed $resultado */
/** @var mixed $total */
?>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1.5rem;">
    <div>
        <h1 style="margin-bottom: 0.5rem;">Gestión de Horarios</h1>
        <p style="margin: 0; color: #64748b;">Grilla semanal de clases, profesores asignados y cupos disponibles.</p>
    </div>
    <a class="boton" href="/admin/horarios/crear">+ Nuevo Horario / Carga Múltiple</a>
</div>

<?php
$mensaje = obtenerMensaje($resultado);
if($mensaje):
    if((int)$resultado === 1 && isset($total) && (int)$total > 1) {
        $mensaje = "Se crearon {$total} turnos correctamente";
    }
?>
    <div class="alerta exito"><?php echo s($mensaje); ?></div>
<?php endif; ?>

<?php if(empty($horarios)): ?>
    <div style="background: white; padding: 4rem; text-align: center; border-radius: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-top: 2rem;">
        <h3 style="color: #0b0f19;">No hay horarios cargados todavía</h3>
        <p style="color: #64748b; margin-bottom: 2rem;">Podés cargar turnos individuales o franjas horarias automáticas para varios días a la vez.</p>
        <a class="boton" href="/admin/horarios/crear">Cargar Primer Horario</a>
    </div>
<?php else: ?>
    <?php 
        $esAdmin = true;
        include __DIR__ . '/../../templates/grilla-horarios.php'; 
    ?>
<?php endif; ?>
