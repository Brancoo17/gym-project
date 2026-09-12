<?php
/** @var \Model\Plan $plan */
/** @var array $alertas */
?>

<h1>Actualizar Plan</h1>
<a class="boton" href="/admin/planes" style="margin-bottom: 2rem;">&larr; Volver a Planes</a>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<form class="formulario" method="POST" enctype="multipart/form-data">
    <?php include_once __DIR__ . '/formulario.php'; ?>
    <input type="submit" class="boton" value="Guardar Cambios">
</form>
