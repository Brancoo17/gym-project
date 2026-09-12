<div class="campo">
    <label for="selector_plan">Disciplina / Plan:</label>
    <select id="selector_plan" name="rutina[plan_id]" onchange="cambiarTipoPlan(this)" required>
        <option value="" disabled <?php echo empty($rutina->plan_id) ? 'selected' : ''; ?>>-- Seleccioná el plan --</option>
        <?php foreach($planes as $p): 
            $esCross = (($p->tipo_disciplina ?? '') === 'crossfit' || stripos($p->nombre, 'crossfit') !== false) ? '1' : '0';
            $esMuscu = (($p->tipo_disciplina ?? '') === 'musculacion' || stripos($p->nombre, 'musculaci') !== false) ? '1' : '0';
            $labelDisciplina = $esCross === '1' ? 'Crossfit / WOD' : ($esMuscu === '1' ? 'Musculación' : 'Otro / Pileta / Especial');
        ?>
            <option value="<?php echo $p->id; ?>" 
                    data-crossfit="<?php echo $esCross; ?>"
                    data-musculacion="<?php echo $esMuscu; ?>"
                    <?php echo (int)$rutina->plan_id === (int)$p->id ? 'selected' : ''; ?>>
                <?php echo s($p->nombre); ?> (<?php echo $labelDisciplina; ?>)
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="rutinas-form-grid">
    <div class="campo">
        <label for="fecha">Fecha del Entrenamiento:</label>
        <input type="date" 
               id="fecha" 
               name="rutina[fecha]" 
               value="<?php echo s($rutina->fecha ?: date('Y-m-d')); ?>" 
               required>
    </div>

    <div class="campo">
        <label for="nombre">Título de la Rutina / WOD:</label>
        <input type="text" 
               id="nombre" 
               name="rutina[nombre]" 
               placeholder="Ej: WOD Murph, Día 1 - Torso Pesado, Circuito Metcon..." 
               value="<?php echo s($rutina->nombre); ?>" 
               required>
    </div>
</div>

<div class="rutinas-form-grid">
    <div class="campo">
        <label for="entrenador_id">Profesor / Entrenador a Cargo:</label>
        <select id="entrenador_id" name="rutina[entrenador_id]" required>
            <?php foreach($entrenadores as $e): ?>
                <option value="<?php echo $e->id; ?>" <?php echo (int)$rutina->entrenador_id === (int)$e->id ? 'selected' : ''; ?>>
                    <?php echo s($e->nombre . ' ' . $e->apellido); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="campo campo-destinatario">
        <label>Destinatario de la Rutina:</label>

        <!-- Checkbox de Rutina Grupal -->
        <div class="destinatario-opcion-grupal">
            <label class="checkbox-grupal-label" for="check_rutina_grupal">
                <input type="checkbox" 
                       id="check_rutina_grupal" 
                       name="check_rutina_grupal"
                       value="1"
                       <?php echo empty($rutina->cliente_id) ? 'checked' : ''; ?> 
                       onchange="toggleTipoDestinatario(this.checked)">
                <span class="checkbox-texto">
                    <i class="fa-solid fa-users"></i>
                    <strong>Todos los alumnos de la clase (Rutina Grupal)</strong>
                </span>
            </label>
        </div>

        <!-- Mensaje informativo cuando está activa la opción grupal -->
        <div id="info_rutina_grupal" class="destinatario-badge-grupal" style="<?php echo empty($rutina->cliente_id) ? 'display: flex;' : 'display: none;'; ?>">
            <i class="fa-solid fa-circle-check"></i>
            <span>Esta rutina se publicará en la pizarra para todos los alumnos inscriptos al plan.</span>
        </div>

        <!-- Buscador de alumnos para rutina individual o multi-alumno -->
        <div id="contenedor_buscador_alumno" class="destinatario-buscador-contenedor" style="<?php echo !empty($rutina->cliente_id) ? 'display: block;' : 'display: none;'; ?>">
            <div class="destinatario-buscador-header">
                <label for="input_buscar_alumno" class="label-subcampo">
                    <?php echo !empty($rutina->id) ? 'Buscar y asignar alumno:' : 'Buscar y asignar alumnos (podés sumar más de uno):'; ?>
                </label>
                <span id="contador_alumnos_seleccionados" class="badge-contador-alumnos" style="display: none;">
                    <i class="fa-solid fa-user-check"></i> <span id="num_alumnos_seleccionados">0</span> seleccionados
                </span>
            </div>

            <div class="destinatario-input-wrapper" id="destinatario_input_box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" 
                       id="input_buscar_alumno" 
                       placeholder="<?php echo !empty($rutina->id) ? 'Escribí nombre, apellido o email para reasignar el alumno...' : 'Escribí nombre, apellido o email para buscar y agregar alumnos...'; ?>" 
                       autocomplete="off"
                       oninput="filtrarAlumnos(this.value)"
                       onfocus="filtrarAlumnos(this.value)">
                <button type="button" class="btn-limpiar-busqueda" id="btn_limpiar_busqueda" onclick="limpiarBusquedaAlumno()" style="display: none;" title="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Dropdown con resultados de búsqueda -->
            <div id="dropdown_alumnos" class="dropdown-alumnos" style="display: none;">
                <div id="lista_alumnos_sugerencias"></div>
                <div id="alumnos_sin_resultados" style="display: none; padding: 1.2rem; text-align: center; color: #94a3b8; font-size: 1.35rem;">
                    <i class="fa-solid fa-user-slash" style="margin-right: 0.5rem;"></i> No se encontraron alumnos con ese nombre o email.
                </div>
            </div>

            <!-- Contenedor de alumnos seleccionados (Chips / Tarjetas) -->
            <div id="contenedor_alumnos_seleccionados" class="contenedor-alumnos-seleccionados" style="display: none;">
                <div class="alumnos-seleccionados-header">
                    <span class="alumnos-seleccionados-titulo">
                        <i class="fa-solid fa-users-viewfinder"></i> Alumnos asignados:
                    </span>
                    <?php if(empty($rutina->id)): ?>
                        <button type="button" class="btn-quitar-todos" onclick="deseleccionarTodosAlumnos()" title="Quitar todos los alumnos">
                            <i class="fa-solid fa-trash-can"></i> Quitar todos
                        </button>
                    <?php endif; ?>
                </div>
                <div id="lista_chips_alumnos" class="lista-chips-alumnos">
                    <!-- Se generan dinámicamente con JS -->
                </div>
            </div>

            <!-- Contenedor de inputs hidden clientes_ids[] que viajan en el POST -->
            <div id="contenedor_hidden_clientes">
                <input type="hidden" id="cliente_id" name="rutina[cliente_id]" value="<?php echo !empty($rutina->cliente_id) ? (int)$rutina->cliente_id : ''; ?>">
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- SECCIÓN EXCLUSIVA CROSSFIT (PARÁMETROS WOD) -->
<!-- ========================================== -->
<div id="seccion_parametros_wod" style="background: #fff7ed; border: 2px solid #ffedd5; border-radius: 1.2rem; padding: 2.2rem; margin: 2.5rem 0; display: none;">
    <h3 style="font-size: 1.7rem; color: #c2410c; margin: 0 0 1.5rem 0; display: flex; align-items: center; gap: 0.8rem;">
        <i class="fa-solid fa-stopwatch"></i> Parámetros del WOD (Workout of the Day)
    </h3>

    <div class="rutinas-form-grid">
        <div class="campo">
            <label for="wod_formato">Formato del WOD:</label>
            <select id="wod_formato" name="rutina[wod_formato]">
                <option value="" disabled selected>-- Elegí el formato --</option>
                <?php foreach($formatosWod as $clave => $nombreFormato): ?>
                    <option value="<?php echo $clave; ?>" <?php echo $rutina->wod_formato === $clave ? 'selected' : ''; ?>>
                        <?php echo s($nombreFormato); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="wod_tiempo">Tiempo / Time Cap / Duración:</label>
            <input type="text" 
                   id="wod_tiempo" 
                   name="rutina[wod_tiempo]" 
                   placeholder="Ej: Time Cap 15 min, AMRAP 20 min, EMOM 12 min..." 
                   value="<?php echo s($rutina->wod_tiempo); ?>">
        </div>
    </div>

    <div class="campo" style="margin-bottom: 0;">
        <label for="wod_descripcion">Consigna o Estrategia del WOD (Opcional):</label>
        <textarea id="wod_descripcion" 
                  name="rutina[wod_descripcion]" 
                  rows="2" 
                  placeholder="Ej: Mantener ritmo constante, escalar peso si es necesario..."><?php echo s($rutina->wod_descripcion); ?></textarea>
    </div>
</div>

<!-- ========================================== -->
<!-- CONSTRUCTOR DE BLOQUES DE ENTRENAMIENTO -->
<!-- ========================================== -->
<div style="margin-top: 3rem;">
    <h3 style="font-size: 2rem; color: #0f172a; margin-bottom: 0.5rem;">
        <i class="fa-solid fa-layer-group" style="color: #149b2b;"></i> Ejercicios del Entrenamiento
    </h3>
    <p style="color: #64748b; font-size: 1.4rem; margin-bottom: 2rem;">
        Cargá los ejercicios en cada sección con sus respectivas rondas, series, repeticiones y cargas.
    </p>

    <!-- Contenedor dinámico inyectado por JS -->
    <div id="contenedor_bloques_rutina"></div>

    <!-- Botón para agregar más días (si no es Crossfit) -->
    <div id="btn_agregar_dia_contenedor" style="display: none; margin-top: 1.5rem;">
        <button type="button" class="btn-agregar-ejercicio" onclick="agregarBloqueEstandar()" style="padding: 1rem 1.8rem; font-size: 1.4rem;">
            <i class="fa-solid fa-calendar-plus"></i> + Agregar Otro Día de Entrenamiento
        </button>
    </div>
</div>

<!-- Opciones de Ejercicios pre-renderizadas para clonar en JS -->
<select id="select_ejercicios_base" style="display: none;">
    <option value="" disabled selected>-- Seleccioná un ejercicio --</option>
    <?php 
    $ejerciciosPorGrupo = [];
    foreach($ejercicios as $ej) {
        $ejerciciosPorGrupo[$ej->grupo_muscular][] = $ej;
    }
    foreach($ejerciciosPorGrupo as $grupo => $lista): ?>
        <optgroup label="<?php echo s($grupo); ?>">
            <?php foreach($lista as $ej): ?>
                <option value="<?php echo $ej->id; ?>"><?php echo s($ej->nombre); ?></option>
            <?php endforeach; ?>
        </optgroup>
    <?php endforeach; ?>
</select>

<script>
// Lista de alumnos disponibles provista desde el servidor
const listaAlumnosGym = [
    <?php foreach($clientes as $c): ?>
    {
        id: <?php echo (int)$c->id; ?>,
        nombre: <?php echo json_encode($c->nombre . ' ' . $c->apellido); ?>,
        email: <?php echo json_encode($c->email); ?>
    },
    <?php endforeach; ?>
];

const esModoEdicion = <?php echo !empty($rutina->id) ? 'true' : 'false'; ?>;

// Alumnos inicialmente asignados
<?php 
$alumnosIniciales = [];
if(!empty($rutina->cliente_id)) {
    foreach($clientes as $c) {
        if((int)$c->id === (int)$rutina->cliente_id) {
            $alumnosIniciales[] = [
                'id' => (int)$c->id,
                'nombre' => $c->nombre . ' ' . $c->apellido,
                'email' => $c->email
            ];
            break;
        }
    }
}
?>
let alumnosSeleccionados = <?php echo json_encode($alumnosIniciales); ?>;

function normalizarTextoBusqueda(str) {
    return (str || '')
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim();
}

function toggleTipoDestinatario(esGrupal) {
    const infoGrupal = document.getElementById('info_rutina_grupal');
    const contenedorBuscador = document.getElementById('contenedor_buscador_alumno');

    if(esGrupal) {
        if(infoGrupal) infoGrupal.style.display = 'flex';
        if(contenedorBuscador) contenedorBuscador.style.display = 'none';
    } else {
        if(infoGrupal) infoGrupal.style.display = 'none';
        if(contenedorBuscador) {
            contenedorBuscador.style.display = 'block';
            const input = document.getElementById('input_buscar_alumno');
            if(input && alumnosSeleccionados.length === 0) {
                input.focus();
                filtrarAlumnos('');
            }
        }
    }

    if(typeof onCambioTipoDestinatario === 'function') {
        onCambioTipoDestinatario(esGrupal);
    }
}

function renderizarAlumnosSeleccionados() {
    const contenedor = document.getElementById('contenedor_alumnos_seleccionados');
    const listaChips = document.getElementById('lista_chips_alumnos');
    const contenedorHidden = document.getElementById('contenedor_hidden_clientes');
    const contador = document.getElementById('contador_alumnos_seleccionados');
    const numAlumnos = document.getElementById('num_alumnos_seleccionados');

    if(!listaChips || !contenedorHidden) return;

    listaChips.innerHTML = '';
    contenedorHidden.innerHTML = '';

    if(alumnosSeleccionados.length === 0) {
        if(contenedor) contenedor.style.display = 'none';
        if(contador) contador.style.display = 'none';
        contenedorHidden.innerHTML = '<input type="hidden" id="cliente_id" name="rutina[cliente_id]" value="">';
        return;
    }

    if(contenedor) contenedor.style.display = 'block';
    if(contador) contador.style.display = 'inline-flex';
    if(numAlumnos) numAlumnos.textContent = alumnosSeleccionados.length;

    alumnosSeleccionados.forEach((alumno) => {
        // Generar chip visual
        const chip = document.createElement('div');
        chip.className = 'chip-alumno';
        chip.innerHTML = `
            <div class="chip-alumno__avatar">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div class="chip-alumno__info">
                <strong class="chip-alumno__nombre">${alumno.nombre}</strong>
                <span class="chip-alumno__email">${alumno.email}</span>
            </div>
            <button type="button" class="chip-alumno__btn-quitar" onclick="quitarAlumno(${alumno.id})" title="Quitar alumno">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        listaChips.appendChild(chip);

        // Input hidden clientes_ids[]
        const inputHidden = document.createElement('input');
        inputHidden.type = 'hidden';
        inputHidden.name = 'clientes_ids[]';
        inputHidden.value = alumno.id;
        contenedorHidden.appendChild(inputHidden);
    });

    // Fallback rutina[cliente_id] para compatibilidad con código que lea el primer ID
    const inputFallback = document.createElement('input');
    inputFallback.type = 'hidden';
    inputFallback.id = 'cliente_id';
    inputFallback.name = 'rutina[cliente_id]';
    inputFallback.value = alumnosSeleccionados[0] ? alumnosSeleccionados[0].id : '';
    contenedorHidden.appendChild(inputFallback);
}

function filtrarAlumnos(termino) {
    const dropdown = document.getElementById('dropdown_alumnos');
    const listaSug = document.getElementById('lista_alumnos_sugerencias');
    const sinResultados = document.getElementById('alumnos_sin_resultados');
    const btnLimpiar = document.getElementById('btn_limpiar_busqueda');

    if(!dropdown || !listaSug) return;

    if(btnLimpiar) {
        btnLimpiar.style.display = (termino && termino.trim() !== '') ? 'block' : 'none';
    }

    const q = normalizarTextoBusqueda(termino);
    let filtrados = [];

    if(!q) {
        filtrados = listaAlumnosGym.slice(0, 10);
    } else {
        filtrados = listaAlumnosGym.filter(a => {
            const nom = normalizarTextoBusqueda(a.nombre);
            const em = normalizarTextoBusqueda(a.email);
            return nom.includes(q) || em.includes(q);
        });
    }

    listaSug.innerHTML = '';

    if(filtrados.length === 0) {
        if(sinResultados) sinResultados.style.display = 'block';
    } else {
        if(sinResultados) sinResultados.style.display = 'none';
        filtrados.forEach(alumno => {
            const yaSeleccionado = alumnosSeleccionados.some(a => a.id === alumno.id);

            const item = document.createElement('div');
            item.className = `item-alumno-sugerencia ${yaSeleccionado ? 'item-alumno-sugerencia--seleccionado' : ''}`;
            
            const badgeTexto = yaSeleccionado 
                ? '<i class="fa-solid fa-check"></i> Agregado' 
                : (esModoEdicion ? '<i class="fa-solid fa-user-pen"></i> Asignar' : '<i class="fa-solid fa-plus"></i> Sumar');

            item.innerHTML = `
                <div class="item-alumno-sugerencia__info">
                    <strong><i class="fa-solid fa-user" style="color: #64748b; margin-right: 0.5rem;"></i>${alumno.nombre}</strong>
                    <span>${alumno.email}</span>
                </div>
                <span class="item-alumno-sugerencia__badge">${badgeTexto}</span>
            `;

            item.onclick = function() {
                if(yaSeleccionado && !esModoEdicion) {
                    quitarAlumno(alumno.id);
                } else {
                    seleccionarAlumno(alumno.id, alumno.nombre, alumno.email);
                }
            };
            listaSug.appendChild(item);
        });
    }

    dropdown.style.display = 'block';
}

function seleccionarAlumno(id, nombre, email) {
    if(esModoEdicion) {
        alumnosSeleccionados = [{id: id, nombre: nombre, email: email}];
    } else {
        const existe = alumnosSeleccionados.some(a => a.id === id);
        if(!existe) {
            alumnosSeleccionados.push({id: id, nombre: nombre, email: email});
        }
    }

    renderizarAlumnosSeleccionados();

    const input = document.getElementById('input_buscar_alumno');
    if(input) {
        input.value = '';
        input.focus();
    }
    const btnLimpiar = document.getElementById('btn_limpiar_busqueda');
    if(btnLimpiar) btnLimpiar.style.display = 'none';

    // Cerrar dropdown luego de seleccionar
    const dropdown = document.getElementById('dropdown_alumnos');
    if(dropdown) dropdown.style.display = 'none';
}

function quitarAlumno(id) {
    alumnosSeleccionados = alumnosSeleccionados.filter(a => a.id !== id);
    renderizarAlumnosSeleccionados();

    // Si el dropdown estaba abierto, actualizarlo
    const dropdown = document.getElementById('dropdown_alumnos');
    if(dropdown && dropdown.style.display === 'block') {
        const input = document.getElementById('input_buscar_alumno');
        filtrarAlumnos(input ? input.value : '');
    }
}

function deseleccionarTodosAlumnos() {
    alumnosSeleccionados = [];
    renderizarAlumnosSeleccionados();

    const dropdown = document.getElementById('dropdown_alumnos');
    if(dropdown && dropdown.style.display === 'block') {
        const input = document.getElementById('input_buscar_alumno');
        filtrarAlumnos(input ? input.value : '');
    }
}

function limpiarBusquedaAlumno() {
    const input = document.getElementById('input_buscar_alumno');
    if(input) {
        input.value = '';
        input.focus();
        filtrarAlumnos('');
    }
}

// Cerrar dropdown al clickear fuera
document.addEventListener('click', function(e) {
    const contenedor = document.getElementById('contenedor_buscador_alumno');
    const dropdown = document.getElementById('dropdown_alumnos');
    if(contenedor && dropdown && !contenedor.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// Validación al enviar el formulario
document.addEventListener('DOMContentLoaded', function() {
    renderizarAlumnosSeleccionados();

    const inputBuscar = document.getElementById('input_buscar_alumno');
    if(inputBuscar) {
        inputBuscar.addEventListener('keydown', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
                const primerItemNoAgregado = document.querySelector('#lista_alumnos_sugerencias .item-alumno-sugerencia:not(.item-alumno-sugerencia--seleccionado)');
                if(primerItemNoAgregado) {
                    primerItemNoAgregado.click();
                }
            }
        });
    }

    const form = document.getElementById('form_rutina');
    if(form) {
        form.addEventListener('submit', function(e) {
            const checkGrupal = document.getElementById('check_rutina_grupal');

            if(checkGrupal && !checkGrupal.checked) {
                if(alumnosSeleccionados.length === 0) {
                    e.preventDefault();
                    alert('Has seleccionado rutina individual/personalizada pero no has agregado ningún alumno. Por favor, buscá y seleccioná al menos un alumno o marcá la opción de "Rutina Grupal".');
                    const input = document.getElementById('input_buscar_alumno');
                    if(input) {
                        input.focus();
                        filtrarAlumnos('');
                    }
                    return false;
                }
            }
        });
    }
});
</script>
