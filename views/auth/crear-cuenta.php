<?php
/** @var \Model\Usuario $usuario */
/** @var array $alertas */
?>

<h1 class="nombre-pagina">Crear cuenta</h1>
<p class="descripcion-pagina">Completá el formulario para ser cliente</p>

<?php include_once __DIR__ . '/../templates/alertas.php'; ?>

<form class="formulario" method="POST" action="/crear-cuenta">
    <div class="campo">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" value="<?php echo s($usuario->nombre); ?>">
    </div>
    <div class="campo">
        <label for="apellido">Apellido</label>
        <input type="text" id="apellido" name="apellido" placeholder="Tu apellido" value="<?php echo s($usuario->apellido); ?>">
    </div>
    <div class="campo">
        <label for="dni">DNI / Documento</label>
        <input type="text" id="dni" name="dni" placeholder="Tu número de documento" inputmode="numeric" value="<?php echo s($usuario->dni ?? ''); ?>">
    </div>
    <div class="campo">
        <label for="telefono">Teléfono</label>
        <input type="tel" id="telefono" name="telefono" placeholder="Tu teléfono" value="<?php echo s($usuario->telefono); ?>">
    </div>
    <div class="campo">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Tu email" value="<?php echo s($usuario->email); ?>">
    </div>
    <div class="campo">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Tu password">
    </div>
    <div class="campo">
        <label for="password2">Repetir password</label>
        <input type="password" id="password2" name="password2" placeholder="Repetí tu password">
    </div>

    <input type="submit" class="boton" value="Crear cuenta">
</form>

<div class="acciones">
    <a href="/login">¿Ya tenés cuenta? Iniciar sesión</a>
    <a href="/olvide">¿Olvidaste tu password?</a>
</div>
