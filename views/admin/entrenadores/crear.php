<?php
/** @var \Model\Usuario $entrenador */
/** @var array $alertas */
?>

<div class="entrenadores-header">
    <div>
        <h1>Registrar Entrenador</h1>
        <p>Completá los datos para dar de alta a un nuevo profesor en el staff.</p>
    </div>
    <a href="/admin/entrenadores" class="boton">
        &larr; Volver a Entrenadores
    </a>
</div>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<div class="form-entrenador-card">
    <form class="formulario" method="POST" action="/admin/entrenadores/crear">
        <?php include_once __DIR__ . '/formulario.php'; ?>

        <div style="margin-top: 3rem;">
            <input type="submit" class="boton" value="Registrar Entrenador">
        </div>
    </form>
</div>
