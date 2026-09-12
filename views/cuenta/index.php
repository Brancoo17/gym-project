<?php
/** @var \Model\Usuario $usuario */
/** @var array $alertas */
$rol = $_SESSION['rol'] ?? 'cliente';
$urlVolver = match($rol) {
    'admin' => '/admin',
    'entrenador' => '/entrenador',
    default => '/cliente'
};
$rolNombre = match($rol) {
    'admin' => 'Administrador',
    'entrenador' => 'Entrenador / Profesor',
    default => 'Cliente / Alumno'
};
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Mi Cuenta</h1>
        <p>Gestioná tus datos personales de contacto y actualizá tus credenciales de acceso al sistema.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="<?php echo $urlVolver; ?>" class="boton-accion">
            <i class="fa-solid fa-arrow-left"></i> Volver al Panel
        </a>
    </div>
</div>

<?php include_once __DIR__ . '/../templates/alertas.php'; ?>

<div class="cuenta-grid">

    <!-- Tarjeta 1: Datos Personales -->
    <div class="config-seccion-card">
        <div class="config-seccion-card__header">
            <h3><i class="fa-solid fa-user-pen icono--verde"></i> Datos Personales</h3>
            <p>Información básica de perfil y vías de comunicación registradas.</p>
        </div>

        <form class="config-seccion-card__cuerpo formulario" method="POST" action="/cuenta">
            <input type="hidden" name="action" value="datos">

            <div class="cuenta-rol-badge-box">
                <span class="cuenta-rol-label">Tipo de Cuenta:</span>
                <span class="cuenta-rol-badge cuenta-rol-badge--<?php echo s($rol); ?>">
                    <i class="fa-solid fa-id-badge"></i> <?php echo s($rolNombre); ?>
                </span>
            </div>

            <div class="cuenta-campos-doble">
                <div class="campo">
                    <label for="nombre">Nombre <span class="campo-obligatorio">*</span></label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="<?php echo s($usuario->nombre); ?>" 
                           placeholder="Tu nombre" 
                           required 
                           maxlength="60">
                </div>

                <div class="campo">
                    <label for="apellido">Apellido <span class="campo-obligatorio">*</span></label>
                    <input type="text" 
                           id="apellido" 
                           name="apellido" 
                           value="<?php echo s($usuario->apellido); ?>" 
                           placeholder="Tu apellido" 
                           required 
                           maxlength="60">
                </div>
            </div>

            <?php if($rol === 'cliente'): ?>
                <div class="campo">
                    <label for="dni">DNI / Documento <span class="campo-obligatorio">*</span></label>
                    <input type="text" 
                           id="dni" 
                           name="dni" 
                           value="<?php echo s($usuario->dni ?? ''); ?>" 
                           placeholder="Ej. 40123456" 
                           inputmode="numeric"
                           required 
                           maxlength="20">
                    <span class="campo-ayuda">
                        <i class="fa-solid fa-id-card"></i> Tu documento nacional de identidad registrado como cliente del gimnasio.
                    </span>
                </div>
            <?php endif; ?>

            <div class="campo">
                <label for="telefono">Teléfono <span class="campo-obligatorio">*</span></label>
                <input type="tel" 
                       id="telefono" 
                       name="telefono" 
                       value="<?php echo s($usuario->telefono); ?>" 
                       placeholder="Ej. 1123456789" 
                       required 
                       maxlength="20">
                <span class="campo-ayuda">
                    <i class="fa-solid fa-phone"></i> Utilizado para recordatorios y avisos importantes de reservas.
                </span>
            </div>

            <div class="campo">
                <label for="email">Correo Electrónico <span class="campo-obligatorio">*</span></label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo s($usuario->email); ?>" 
                       placeholder="tuemail@ejemplo.com" 
                       required 
                       maxlength="60">
                <span class="campo-ayuda">
                    <i class="fa-solid fa-envelope"></i> Es tu usuario para ingresar al gimnasio y recibir notificaciones.
                </span>
            </div>

            <div class="cuenta-form-accion">
                <button type="submit" class="boton">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Datos Personales
                </button>
            </div>
        </form>
    </div>

    <!-- Tarjeta 2: Seguridad y Contraseña -->
    <div class="config-seccion-card config-seccion-card--azul">
        <div class="config-seccion-card__header">
            <h3><i class="fa-solid fa-shield-halved icono--azul"></i> Seguridad y Contraseña</h3>
            <p>Actualizá tu contraseña de inicio de sesión para mantener tu cuenta protegida.</p>
        </div>

        <form class="config-seccion-card__cuerpo formulario" method="POST" action="/cuenta">
            <input type="hidden" name="action" value="password">

            <div class="campo">
                <label for="password_actual">Contraseña Actual <span class="campo-obligatorio">*</span></label>
                <input type="password" 
                       id="password_actual" 
                       name="password_actual" 
                       placeholder="Ingresá tu contraseña actual" 
                       required>
                <span class="campo-ayuda">
                    <i class="fa-solid fa-lock"></i> Requerida por seguridad para validar tu identidad.
                </span>
            </div>

            <div class="campo">
                <label for="password_nuevo">Nueva Contraseña <span class="campo-obligatorio">*</span></label>
                <input type="password" 
                       id="password_nuevo" 
                       name="password_nuevo" 
                       placeholder="Mínimo 6 caracteres" 
                       required 
                       minlength="6">
            </div>

            <div class="campo">
                <label for="password_confirmar">Confirmar Nueva Contraseña <span class="campo-obligatorio">*</span></label>
                <input type="password" 
                       id="password_confirmar" 
                       name="password_confirmar" 
                       placeholder="Reescribí la nueva contraseña" 
                       required 
                       minlength="6">
            </div>

            <div class="config-badge-recomendacion">
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Recomendación de seguridad:</strong> Usá al menos 6 caracteres combinando letras y números. Evitá usar datos fáciles de adivinar.
                </div>
            </div>

            <div class="cuenta-form-accion">
                <button type="submit" class="boton boton-accion">
                    <i class="fa-solid fa-key"></i> Actualizar Contraseña
                </button>
            </div>
        </form>
    </div>

</div>
