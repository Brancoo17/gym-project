<?php
/** @var \Model\Plan $plan */
?>

<div class="campo">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="plan[nombre]" placeholder="Nombre del plan" value="<?php echo s($plan->nombre); ?>">
</div>

<div class="campo">
    <label for="tipo_disciplina">Tipo de Disciplina / Metodología de Rutina:</label>
    <select id="tipo_disciplina" name="plan[tipo_disciplina]">
        <option value="musculacion" <?php echo ($plan->tipo_disciplina ?? 'musculacion') === 'musculacion' ? 'selected' : ''; ?>>
            Musculación / Sala de Pesas / Fitness (Rutinas divididas por días y grupos musculares)
        </option>
        <option value="crossfit" <?php echo ($plan->tipo_disciplina ?? '') === 'crossfit' ? 'selected' : ''; ?>>
            Crossfit / Funcional WOD (Metodología WOD, bloques Core / Warmup / Fuerza / WOD)
        </option>
        <option value="otro" <?php echo ($plan->tipo_disciplina ?? '') === 'otro' ? 'selected' : ''; ?>>
            Otro / Pileta / Actividades Especiales (Pases libres, natación, yoga u otras actividades)
        </option>
    </select>
</div>

<div class="campo">
    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="plan[descripcion]" placeholder="Descripción del plan" rows="4"><?php echo s($plan->descripcion); ?></textarea>
</div>

<div class="campo">
    <label for="precio">Precio ($):</label>
    <input type="number" id="precio" name="plan[precio]" placeholder="Ej: 25000" step="0.01" min="0" value="<?php echo s((string)$plan->precio); ?>">
</div>

<div class="campo">
    <label for="duracion_dias">Duración (días):</label>
    <input type="number" id="duracion_dias" name="plan[duracion_dias]" placeholder="Ej: 30" min="1" value="<?php echo s((string)$plan->duracion_dias); ?>">
</div>

<div class="campo">
    <label for="cantidad_clases">Cantidad de clases mensuales (opcional):</label>
    <input type="number" id="cantidad_clases" name="plan[cantidad_clases]" placeholder="Dejar vacío si son ilimitadas" min="1" value="<?php echo s((string)$plan->cantidad_clases); ?>">
</div>

<div class="campo">
    <label for="imagen">Imagen (WebP):</label>
    <input type="file" id="imagen" name="plan[imagen]" accept="image/jpeg, image/png, image/webp">

    <?php if($plan->imagen): ?>
        <p style="margin-top: 1rem; font-weight: 700;">Imagen actual:</p>
        <img src="/imagenes/<?php echo s($plan->imagen); ?>" alt="Imagen del plan" style="max-width: 220px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <?php endif; ?>
</div>

<div class="campo" style="display: flex; align-items: center; gap: 1rem; margin-top: 2rem;">
    <input type="checkbox" id="activo" name="plan[activo]" value="1" <?php echo $plan->activo ? 'checked' : ''; ?> style="width: auto; margin: 0; cursor: pointer; width: 2rem; height: 2rem;">
    <label for="activo" style="margin: 0; cursor: pointer; font-size: 1.6rem;">Plan activo (visible para los clientes y en la web)</label>
</div>
