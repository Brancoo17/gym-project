<?php
/** @var array $alertas */
?>

<h1 class="nombre-pagina">Olvidé mi password</h1>
<p class="descripcion-pagina">Ingresá tu email para reestablecerlo</p>

<?php include_once __DIR__ . '/../templates/alertas.php'; ?>

<form class="formulario" method="POST" action="/olvide">
    <div class="campo">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Tu email">
    </div>

    <input type="submit" class="boton" value="Enviar instrucciones">
</form>

<div class="acciones">
    <a href="/login">¿Ya tenés cuenta? Iniciar sesión</a>
    <a href="/crear-cuenta">¿No tenés cuenta? Crear una</a>
</div>
