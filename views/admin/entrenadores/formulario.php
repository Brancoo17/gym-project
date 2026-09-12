<?php
/** @var \Model\Usuario $entrenador */
$esEdicion = !empty($entrenador->id);
?>

<div class="campo">
    <label for="nombre">Nombre:</label>
    <input type="text" 
           id="nombre" 
           name="entrenador[nombre]" 
           placeholder="Nombre del entrenador" 
           value="<?php echo s($entrenador->nombre); ?>" 
           required>
</div>

<div class="campo">
    <label for="apellido">Apellido:</label>
    <input type="text" 
           id="apellido" 
           name="entrenador[apellido]" 
           placeholder="Apellido del entrenador" 
           value="<?php echo s($entrenador->apellido); ?>" 
           required>
</div>

<div class="campo">
    <label for="email">Correo Electrónico (Login):</label>
    <input type="email" 
           id="email" 
           name="entrenador[email]" 
           placeholder="profesor@gym.com" 
           value="<?php echo s($entrenador->email); ?>" 
           required>
</div>

<div class="campo">
    <label for="telefono">Teléfono / WhatsApp:</label>
    <input type="tel" 
           id="telefono" 
           name="entrenador[telefono]" 
           placeholder="Ej: 1123456789" 
           value="<?php echo s($entrenador->telefono); ?>" 
           required>
</div>

<div class="campo">
    <label for="password">
        Contraseña <?php echo $esEdicion ? '(Opcional):' : 'de Acceso Inicial:'; ?>
    </label>
    <input type="password" 
           id="password" 
           name="entrenador[password]" 
           placeholder="<?php echo $esEdicion ? 'Completar solo si deseas cambiarla' : 'Mínimo 6 caracteres'; ?>" 
           <?php echo $esEdicion ? '' : 'required'; ?>>
    <small style="display: block; color: #64748b; font-size: 1.3rem; margin-top: 0.5rem;">
        <?php if($esEdicion): ?>
            Dejar en blanco para conservar la contraseña actual del profesor.
        <?php else: ?>
            Esta será la contraseña con la que el entrenador iniciará sesión en su panel.
        <?php endif; ?>
    </small>
</div>
