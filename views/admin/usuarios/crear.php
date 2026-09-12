<?php
/** @var \Model\Usuario $usuario */
/** @var array $alertas */
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Registrar Nuevo Alumno</h1>
        <p>Alta de cliente en mostrador. Ingresá sus datos para habilitar su portal y turnos.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/admin/usuarios" class="boton">
            <i class="fa-solid fa-arrow-left"></i> Volver a Clientes
        </a>
    </div>
</div>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<div class="form-usuario-card">
    <form class="formulario" method="POST" action="/admin/usuarios/crear">
        <?php include_once __DIR__ . '/formulario.php'; ?>

        <div class="formulario__submit-wrapper">
            <a href="/admin/usuarios" class="btn-cancelar">Cancelar</a>
            <input type="submit" class="boton" value="Registrar y Activar Alumno">
        </div>
    </form>
</div>
