<div class="campo">
    <label for="nombre">Nombre del Ejercicio:</label>
    <input type="text" 
           id="nombre" 
           name="ejercicio[nombre]" 
           placeholder="Ej: Press de Banca Plano, Sentadilla con Barra..." 
           value="<?php echo s($ejercicio->nombre); ?>" 
           required>
</div>

<div class="campo">
    <label for="grupo_muscular">Grupo Muscular:</label>
    <select id="grupo_muscular" name="ejercicio[grupo_muscular]" required>
        <option value="" disabled <?php echo empty($ejercicio->grupo_muscular) ? 'selected' : ''; ?>>-- Seleccioná el grupo muscular --</option>
        <?php foreach($grupos as $grupo): ?>
            <option value="<?php echo s($grupo); ?>" <?php echo $ejercicio->grupo_muscular === $grupo ? 'selected' : ''; ?>>
                <?php echo s($grupo); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="campo">
    <label for="descripcion">Descripción y Técnica de Ejecución:</label>
    <textarea id="descripcion" 
              name="ejercicio[descripcion]" 
              rows="4" 
              placeholder="Detallá la postura inicial, respiración, agarre y recomendaciones de seguridad..." 
              required><?php echo s($ejercicio->descripcion); ?></textarea>
</div>

<div class="campo">
    <label for="imagen">Imagen Demostrativa (Opcional):</label>
    <input type="file" 
           id="imagen" 
           name="ejercicio[imagen]" 
           accept="image/jpeg, image/png, image/webp">
    
    <?php if(!empty($ejercicio->imagen)): ?>
        <p class="texto-ayuda-campo">Imagen actual:</p>
        <div class="preview-imagen-ejercicio">
            <img src="/imagenes/<?php echo s($ejercicio->imagen); ?>" alt="Demostración de <?php echo s($ejercicio->nombre); ?>">
        </div>
    <?php endif; ?>

</div>

<div class="campo">
    <label for="video_url">Enlace a Video Demostrativo (Opcional):</label>
    <input type="url" 
           id="video_url" 
           name="ejercicio[video_url]" 
           placeholder="Ej: https://www.youtube.com/watch?v=..." 
           value="<?php echo s($ejercicio->video_url); ?>">
</div>
