<?php
/** @var \Model\Usuario $entrenador */
/** @var array $alertas */
?>

<div class="entrenadores-header">
    <div>
        <h1>Editar Entrenador</h1>
        <p>Modificá los datos del profesor <?php echo s($entrenador->nombre . ' ' . $entrenador->apellido); ?>.</p>
    </div>
    <a href="/admin/entrenadores" class="boton">
        &larr; Volver a Entrenadores
    </a>
</div>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<div class="form-entrenador-card">
    <form class="formulario" method="POST" action="/admin/entrenadores/actualizar?id=<?php echo $entrenador->id; ?>">
        <?php include_once __DIR__ . '/formulario.php'; ?>

        <div style="margin-top: 3rem;">
            <input type="submit" class="boton" value="Guardar Cambios">
        </div>
    </form>
</div>
