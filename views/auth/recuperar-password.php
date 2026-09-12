<?php
/** @var array $alertas */
/** @var bool $error */
?>

<h1 class="nombre-pagina">Reestablecer password</h1>
<p class="descripcion-pagina">Colocá tu nuevo password</p>

<?php include_once __DIR__ . '/../templates/alertas.php'; ?>

<?php if(empty($error)): ?>
<form class="formulario" method="POST">
    <div class="campo">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Tu nuevo password">
    </div>
    <div class="campo">
        <label for="password2">Repetir password</label>
        <input type="password" id="password2" name="password2" placeholder="Repetí tu password">
    </div>

    <input type="submit" class="boton" value="Guardar password">
</form>
<?php endif; ?>

<div class="acciones">
    <a href="/login">Volver a iniciar sesión</a>
</div>
