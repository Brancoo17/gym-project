<?php
/** @var \Model\Horario $horario */
/** @var array $planes */
/** @var array $entrenadores */
/** @var array $alertas */
?>

<h1>Nuevo Horario</h1>
<a class="boton" href="/admin/horarios" style="margin-bottom: 2rem;">&larr; Volver a Horarios</a>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<form class="formulario" method="POST" action="/admin/horarios/crear">
    <?php include_once __DIR__ . '/formulario.php'; ?>
    <input type="submit" class="boton" value="Guardar Horario">
</form>
