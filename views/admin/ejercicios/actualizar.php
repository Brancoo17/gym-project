<div class="entrenadores-header">
    <div>
        <h1>Actualizar Ejercicio</h1>
        <p>Modificá los datos, grupo muscular o material multimedia del ejercicio.</p>
    </div>
    <a href="/admin/ejercicios" class="boton boton--secundario">
        <i class="fa-solid fa-arrow-left"></i> Volver al Catálogo
    </a>
</div>

<?php if(!empty($alertas)): ?>
    <?php foreach($alertas['error'] ?? [] as $error): ?>
        <div class="alerta error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span><?php echo s($error); ?></span>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="form-ejercicio-card">
    <form class="formulario" method="POST" enctype="multipart/form-data">
        <?php include __DIR__ . '/formulario.php'; ?>
        
        <div class="form-ejercicio-card__submit">
            <input type="submit" value="Guardar Cambios" class="boton">
        </div>
    </form>
</div>


