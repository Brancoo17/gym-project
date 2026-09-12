<?php
/** @var \Model\Usuario $usuario */
/** @var array $alertas */
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Editar Datos del Alumno</h1>
        <p>Modificá los datos personales, contraseña o estado de cuenta de <?php echo s($usuario->nombre . ' ' . $usuario->apellido); ?>.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/admin/usuarios/detalle?id=<?php echo $usuario->id; ?>" class="boton">
            <i class="fa-solid fa-address-card"></i> Ver Ficha
        </a>
        <a href="/admin/usuarios" class="boton-accion">
            <i class="fa-solid fa-arrow-left"></i> Volver a Clientes
        </a>
    </div>
</div>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<div class="form-usuario-card">
    <form class="formulario" method="POST" action="/admin/usuarios/actualizar?id=<?php echo $usuario->id; ?>">
        <?php include_once __DIR__ . '/formulario.php'; ?>

        <div class="formulario__submit-wrapper">
            <a href="/admin/usuarios" class="btn-cancelar">Cancelar</a>
            <input type="submit" class="boton" value="Guardar Cambios">
        </div>
    </form>
</div>
