<?php
/** @var \Model\Usuario $usuario */
$esEdicion = !empty($usuario->id);
$esConfirmado = ((int)$usuario->confirmado === 1);
?>

<div class="campo-grid-dos">
    <div class="campo">
        <label for="nombre">Nombre:</label>
        <input type="text" 
               id="nombre" 
               name="usuario[nombre]" 
               placeholder="Nombre del alumno" 
               value="<?php echo s($usuario->nombre); ?>" 
               required>
    </div>

    <div class="campo">
        <label for="apellido">Apellido:</label>
        <input type="text" 
               id="apellido" 
               name="usuario[apellido]" 
               placeholder="Apellido del alumno" 
               value="<?php echo s($usuario->apellido); ?>" 
               required>
    </div>
</div>

<div class="campo-grid-dos">
    <div class="campo">
        <label for="dni">DNI / Documento:</label>
        <input type="text" 
               id="dni" 
               name="usuario[dni]" 
               placeholder="Ej: 40123456" 
               inputmode="numeric"
               value="<?php echo s($usuario->dni ?? ''); ?>" 
               required>
        <small class="campo-ayuda">Documento de identidad del alumno para identificación y control de acceso.</small>
    </div>

    <div class="campo">
        <label for="telefono">Teléfono / WhatsApp:</label>
        <input type="tel" 
               id="telefono" 
               name="usuario[telefono]" 
               placeholder="Ej: 1123456789" 
               value="<?php echo s($usuario->telefono); ?>" 
               required>
        <small class="campo-ayuda">Para contacto rápido y avisos de reservas desde administración.</small>
    </div>
</div>

<div class="campo">
    <label for="email">Correo Electrónico (Login del alumno):</label>
    <input type="email" 
           id="email" 
           name="usuario[email]" 
           placeholder="alumno@ejemplo.com" 
           value="<?php echo s($usuario->email); ?>" 
           required>
    <small class="campo-ayuda">Será el usuario con el que ingresará a reservar turnos y ver rutinas.</small>
</div>

<div class="campo">
    <label for="password">
        Contraseña <?php echo $esEdicion ? '(Opcional):' : 'de Acceso Inicial:'; ?>
    </label>
    <input type="password" 
           id="password" 
           name="usuario[password]" 
           placeholder="<?php echo $esEdicion ? 'Completar solo si deseás cambiarla' : 'Mínimo 6 caracteres'; ?>" 
           <?php echo $esEdicion ? '' : 'required'; ?>>
    <small class="campo-ayuda">
        <?php if($esEdicion): ?>
            Dejar en blanco para mantener la contraseña actual del alumno.
        <?php else: ?>
            Contraseña provisoria o elegida por el alumno al momento del registro.
        <?php endif; ?>
    </small>
</div>

<div class="campo">
    <label>Estado Inicial de la Cuenta:</label>
    <div class="campo-radio-grupo">
        <label class="radio-custom-label radio-custom-label--activa <?php echo $esConfirmado ? 'activo' : ''; ?>">
            <input type="radio" 
                   name="usuario[confirmado]" 
                   value="1" 
                   <?php echo $esConfirmado ? 'checked' : ''; ?>>
            <i class="fa-solid fa-circle-check"></i> Activa / Confirmada
        </label>

        <label class="radio-custom-label radio-custom-label--pendiente <?php echo !$esConfirmado ? 'activo' : ''; ?>">
            <input type="radio" 
                   name="usuario[confirmado]" 
                   value="0" 
                   <?php echo !$esConfirmado ? 'checked' : ''; ?>>
            <i class="fa-solid fa-clock"></i> Pendiente de Confirmar
        </label>
    </div>
    <small class="campo-ayuda">
        En alta presencial en mostrador se recomienda dejarla como "Activa" para que el alumno pueda reservar de inmediato.
    </small>
</div>
