<?php
/** @var \Model\Usuario $usuario */
/** @var array $planes */
/** @var array $alertas */
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Activar Membresía Manual</h1>
        <p>Alumno: <strong><?php echo s($usuario->nombre . ' ' . $usuario->apellido); ?></strong></p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/admin/usuarios/detalle?id=<?php echo $usuario->id; ?>" class="boton">
            <i class="fa-solid fa-arrow-left"></i> Volver a la Ficha
        </a>
    </div>
</div>

<div class="formulario-contenedor" style="max-width: 600px; margin: 0 auto;">
    <?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

    <form class="formulario" method="POST">
        <div class="campo">
            <label for="plan_id">Seleccionar Plan / Disciplina</label>
            <select name="plan_id" id="plan_id" required>
                <option value="" disabled selected>-- Seleccionar Plan --</option>
                <?php foreach($planes as $plan): ?>
                    <option value="<?php echo s($plan->id); ?>">
                        <?php echo s($plan->nombre); ?> - $<?php echo number_format($plan->precio, 0, ',', '.'); ?> 
                        (<?php echo empty($plan->cantidad_clases) ? 'Libre' : s($plan->cantidad_clases) . ' clases'; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="fecha_inicio">Fecha de Inicio de Vigencia</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="alerta alerta--info" style="margin-bottom: 2rem;">
            <i class="fa-solid fa-circle-info"></i> Al guardar, se registrará automáticamente un pago aprobado en efectivo por el monto total del plan seleccionado y la membresía quedará activa.
        </div>

        <input type="submit" value="Confirmar Pago y Activar" class="boton boton--block boton--verde">
    </form>
</div>
