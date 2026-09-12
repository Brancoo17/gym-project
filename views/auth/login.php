<?php
/** @var \Model\Usuario $auth */
/** @var array $alertas */
?>

<h1 class="nombre-pagina">Iniciar sesión</h1>
<p class="descripcion-pagina">Ingresá con tu cuenta</p>

<?php include_once __DIR__ . '/../templates/alertas.php'; ?>

<form class="formulario" method="POST" action="/login">
    <div class="campo">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Tu email" value="<?php echo s($auth->email); ?>">
    </div>

    <div class="campo">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Tu password">
    </div>

    <input type="submit" class="boton" value="Iniciar sesión">
</form>

<div class="acciones">
    <a href="/crear-cuenta">¿No tenés cuenta? Crear una</a>
    <a href="/olvide">¿Olvidaste tu password?</a>
</div>
