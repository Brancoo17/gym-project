<?php
/** @var \Model\Horario $horario */
/** @var array $planes */
/** @var array $entrenadores */
/** @var array $alertas */
/** @var bool $esEdicion */
$esEdicion = $esEdicion ?? false;
?>

<div class="campo">
    <label for="plan_id">Plan / Disciplina:</label>
    <select id="plan_id" name="horario[plan_id]">
        <option value="">-- Seleccionar Plan --</option>
        <?php foreach($planes as $plan): ?>
            <option value="<?php echo $plan->id; ?>" <?php echo ((string)$horario->plan_id === (string)$plan->id) ? 'selected' : ''; ?>>
                <?php echo s($plan->nombre); ?> (<?php echo (int)$plan->activo ? 'Activo' : 'Inactivo'; ?>)
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="campo">
    <label for="entrenador_id">Entrenador a cargo:</label>
    <select id="entrenador_id" name="horario[entrenador_id]">
        <option value="">-- Seleccionar Entrenador --</option>
        <?php foreach($entrenadores as $entrenador): ?>
            <option value="<?php echo $entrenador->id; ?>" <?php echo ((string)$horario->entrenador_id === (string)$entrenador->id) ? 'selected' : ''; ?>>
                <?php echo s($entrenador->nombre . ' ' . $entrenador->apellido); ?> (<?php echo s(ucfirst($entrenador->rol)); ?>)
            </option>
        <?php endforeach; ?>
    </select>
</div>

<?php if($esEdicion): ?>
    <div class="campo">
        <label for="dia_semana">Día de la semana:</label>
        <select id="dia_semana" name="horario[dia_semana]">
            <option value="">-- Seleccionar Día --</option>
            <?php 
                $dias = [
                    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                    4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'
                ];
                foreach($dias as $num => $nombre): 
            ?>
                <option value="<?php echo $num; ?>" <?php echo ((string)$horario->dia_semana === (string)$num) ? 'selected' : ''; ?>>
                    <?php echo $nombre; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php else: ?>
    <div class="campo">
        <label>Días de la semana (podés seleccionar varios):</label>
        <div class="dias-checkboxes">
            <?php 
                $dias = [
                    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                    4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'
                ];
                $diasSeleccionados = $diasSeleccionados ?? ($horario->dia_semana !== '' ? [(int)$horario->dia_semana] : [1, 3, 5]);
                foreach($dias as $num => $nombre): 
            ?>
                <label class="dia-chip">
                    <input type="checkbox" name="dias_semana[]" value="<?php echo $num; ?>" <?php echo in_array($num, $diasSeleccionados) ? 'checked' : ''; ?>>
                    <span><?php echo $nombre; ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <small style="color: #64748b; display: block; margin-top: 0.6rem;">Si marcás varios días, se creará este turno para todos los días seleccionados en un solo clic.</small>
    </div>
<?php endif; ?>

<div class="campo">
    <label for="hora_inicio">Hora de inicio:</label>
    <input type="time" id="hora_inicio" name="horario[hora_inicio]" value="<?php echo s(substr($horario->hora_inicio, 0, 5)); ?>" required>
</div>

<div class="campo">
    <label for="hora_fin"><?php echo $esEdicion ? 'Hora de finalización:' : 'Hora de finalización (o fin de la franja horaria):'; ?></label>
    <input type="time" id="hora_fin" name="horario[hora_fin]" value="<?php echo s(substr($horario->hora_fin, 0, 5)); ?>" required>
</div>

<?php if(!$esEdicion): ?>
    <div class="campo campo-bloques" style="background: #f1f5f9; padding: 1.5rem; border-radius: 0.8rem; border-left: 4px solid #149b2b;">
        <label style="display: flex; align-items: center; gap: 0.8rem; cursor: pointer; margin-bottom: 0.8rem; font-weight: 700;">
            <input type="checkbox" id="generar_bloques" name="generar_bloques" value="1" style="width: auto; height: 1.8rem; width: 1.8rem;">
            Dividir franja en turnos continuos por intervalo
        </label>
        <div id="opciones_bloques" style="display: none; margin-top: 1rem;">
            <label for="duracion_minutos" style="font-size: 1.4rem;">Duración de cada clase (minutos):</label>
            <select id="duracion_minutos" name="duracion_minutos" style="max-width: 25rem;">
                <option value="45">45 minutos</option>
                <option value="50">50 minutos</option>
                <option value="60" selected>60 minutos (1 hora)</option>
                <option value="90">90 minutos (1 h 30 min)</option>
                <option value="120">120 minutos (2 horas)</option>
            </select>
            <small style="color: #64748b; display: block; margin-top: 0.5rem;">Ejemplo: de 08:00 a 12:00 con 60 min generará 4 turnos: 08-09, 09-10, 10-11 y 11-12.</small>
        </div>
    </div>
<?php endif; ?>

<div class="campo">
    <label for="cupo">Cupo máximo de alumnos por turno (opcional):</label>
    <input type="number" id="cupo" name="horario[cupo]" placeholder="Dejar vacío para ilimitado (ej: 20)" min="1" value="<?php echo s((string)($horario->cupo ?? '')); ?>">
    <small style="color: #64748b; display: block; margin-top: 0.6rem;">Si no ingresás ningún valor, el cupo para este turno será ilimitado.</small>
</div>

<div class="campo">
    <label for="descripcion">Descripción / Modalidad del turno (opcional):</label>
    <textarea id="descripcion" name="horario[descripcion]" rows="2" placeholder="Ej: Turno libre de corrido, Open Box, sin intervalos de clase..."><?php echo s((string)($horario->descripcion ?? '')); ?></textarea>
    <small style="color: #64748b; display: block; margin-top: 0.6rem;">Aclaraciones sobre la modalidad del turno para los alumnos (ej: horario libre, open box, etc.).</small>
</div>

