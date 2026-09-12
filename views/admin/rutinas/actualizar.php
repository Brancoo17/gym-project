<div class="entrenadores-header">
    <div>
        <h1>Actualizar Rutina / WOD</h1>
        <p>Modificá los ejercicios, cargas, series o parámetros del WOD.</p>
    </div>
    <a href="/admin/rutinas" class="boton boton--secundario">
        <i class="fa-solid fa-arrow-left"></i> Volver a Rutinas
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

<div class="constructor-card">
    <form class="formulario" method="POST" id="form_rutina">
        <?php include __DIR__ . '/formulario.php'; ?>

        <div style="margin-top: 3.5rem;">
            <input type="submit" value="Guardar Cambios del Entrenamiento" class="boton">
        </div>
    </form>
</div>

<script>
let indiceEjercicio = 0;
let contadorDiasEstandar = 0;

// Datos precargados de la rutina
const ejerciciosPrecargados = <?php echo json_encode($ejerciciosAsignados ?? []); ?>;

function getOpcionesEjerciciosHTML(ejercicioIdSeleccionado = null) {
    const base = document.getElementById('select_ejercicios_base');
    if(!base) return '';
    let html = base.innerHTML;
    if(ejercicioIdSeleccionado) {
        html = html.replace(`value="${ejercicioIdSeleccionado}"`, `value="${ejercicioIdSeleccionado}" selected`);
    }
    return html;
}

function cambiarTipoPlan(select) {
    const opcion = select.options[select.selectedIndex];
    const esCrossfit = opcion.getAttribute('data-crossfit') === '1';

    const seccionWod = document.getElementById('seccion_parametros_wod');
    const contenedorBloques = document.getElementById('contenedor_bloques_rutina');
    const btnAgregarDia = document.getElementById('btn_agregar_dia_contenedor');

    contenedorBloques.innerHTML = '';
    indiceEjercicio = 0;
    contadorDiasEstandar = 0;

    if(esCrossfit) {
        seccionWod.style.display = 'block';
        btnAgregarDia.style.display = 'none';
        construirBloquesCrossfit();
    } else {
        seccionWod.style.display = 'none';
        btnAgregarDia.style.display = 'block';
        agregarBloqueEstandar();
    }
}

function construirBloquesCrossfit() {
    const contenedor = document.getElementById('contenedor_bloques_rutina');

    // Identificar rondas precargadas
    const rondasCore = ejerciciosPrecargados.find(e => e.bloque === 'core')?.rondas || 3;
    const rondasWarmup = ejerciciosPrecargados.find(e => e.bloque === 'warmup')?.rondas || 3;
    const rondasFuerza = ejerciciosPrecargados.find(e => e.bloque === 'fuerza')?.rondas || 4;
    const rondasWod = ejerciciosPrecargados.find(e => e.bloque === 'wod')?.rondas || '';

    crearBloqueCrossfit(contenedor, 'core', '1° Bloque: Core / Zona Media', 'fa-fire', 'Cantidad de Rondas:', true, false, rondasCore);
    crearBloqueCrossfit(contenedor, 'warmup', '2° Bloque: Warmup (Calentamiento)', 'fa-person-running', 'Cantidad de Rondas:', true, false, rondasWarmup);
    crearBloqueCrossfit(contenedor, 'fuerza', '3° Bloque: Fuerza / Skill', 'fa-dumbbell', 'Cantidad de Rondas:', true, true, rondasFuerza);
    crearBloqueCrossfit(contenedor, 'wod', '4° Bloque: WOD (Workout of the Day)', 'fa-stopwatch', 'Cantidad de Rondas (opcional):', true, false, rondasWod);

    // Renderizar ejercicios existentes
    if(ejerciciosPrecargados.length > 0) {
        ejerciciosPrecargados.forEach(item => {
            const b = item.bloque || 'wod';
            const esFuerza = b === 'fuerza';
            agregarFilaEjercicio(b, esFuerza, item);
        });
    } else {
        agregarFilaEjercicio('core', false);
        agregarFilaEjercicio('warmup', false);
        agregarFilaEjercicio('fuerza', true);
        agregarFilaEjercicio('wod', false);
    }
}

function actualizarRondasBloque(idBloque, valor) {
    const inputs = document.querySelectorAll(`.input-rondas-${idBloque}`);
    inputs.forEach(i => i.value = valor);
}

function crearBloqueCrossfit(contenedor, idBloque, titulo, icono, labelRondas, pideRondas, esFuerza, valorRondas) {
    const card = document.createElement('div');
    card.className = 'bloque-rutina';
    card.id = `bloque_${idBloque}`;

    let rondasHTML = '';
    if(pideRondas) {
        rondasHTML = `
            <div class="bloque-rutina__parametros">
                <label>${labelRondas}</label>
                <input type="number" 
                       id="rondas_${idBloque}" 
                       placeholder="${idBloque === 'wod' ? 'Ej: 5 (opcional)' : 'Ej: 3'}" 
                       min="1" 
                       value="${valorRondas !== null && valorRondas !== undefined ? valorRondas : ''}"
                       oninput="actualizarRondasBloque('${idBloque}', this.value)">
            </div>
        `;
    }

    card.innerHTML = `
        <div class="bloque-rutina__header">
            <h4><i class="fa-solid ${icono}"></i> ${titulo}</h4>
            ${rondasHTML}
        </div>
        <div class="bloque-rutina__ejercicios" id="lista_ejercicios_${idBloque}"></div>
        <button type="button" class="btn-agregar-ejercicio" onclick="agregarFilaEjercicio('${idBloque}', ${esFuerza})">
            <i class="fa-solid fa-plus"></i> + Agregar Ejercicio a este bloque
        </button>
    `;

    contenedor.appendChild(card);
}

function esRutinaIndividual() {
    const checkGrupal = document.getElementById('check_rutina_grupal');
    return checkGrupal ? !checkGrupal.checked : false;
}

function agregarFilaEjercicio(idBloque, esFuerza, datos = {}) {
    const lista = document.getElementById(`lista_ejercicios_${idBloque}`);
    if(!lista) return;

    const idx = indiceEjercicio++;
    const rondasInput = document.getElementById(`rondas_${idBloque}`);
    const rondasVal = (datos.rondas !== undefined && datos.rondas !== null && datos.rondas !== '') 
        ? datos.rondas 
        : (rondasInput ? rondasInput.value : '');

    const fila = document.createElement('div');
    fila.className = `fila-ejercicio-builder ${esFuerza ? '' : 'fila-ejercicio-builder--estandar'}`;

    const opcionesEj = getOpcionesEjerciciosHTML(datos.ejercicio_id || null);
    const esIndividual = esRutinaIndividual();

    if(esFuerza) {
        let pesosHTML = '';
        if(esIndividual) {
            pesosHTML = `
                <div class="campo-ejercicio campo-peso-rx" style="flex: 2;">
                    <label>Peso / Carga Sugerida (Rx)</label>
                    <input type="text" name="ejercicios[${idx}][peso_hombres]" placeholder="Ej: 50 kg" value="${datos.peso_hombres || datos.peso_mujeres || ''}">
                </div>
            `;
        } else {
            pesosHTML = `
                <div class="campo-ejercicio campo-peso-rx">
                    <label>Peso H (Rx)</label>
                    <input type="text" name="ejercicios[${idx}][peso_hombres]" placeholder="Ej: 50 kg" value="${datos.peso_hombres || ''}">
                </div>
                <div class="campo-ejercicio campo-peso-rx campo-peso-rx--mujeres">
                    <label>Peso M (Rx)</label>
                    <input type="text" name="ejercicios[${idx}][peso_mujeres]" placeholder="Ej: 35 kg" value="${datos.peso_mujeres || ''}">
                </div>
            `;
        }

        fila.innerHTML = `
            <div class="campo-ejercicio">
                <label>Ejercicio</label>
                <select name="ejercicios[${idx}][ejercicio_id]" required>
                    ${opcionesEj}
                </select>
            </div>
            <div class="campo-ejercicio">
                <label>Series</label>
                <input type="number" name="ejercicios[${idx}][series]" placeholder="Ej: 4" min="1" value="${datos.series || ''}">
            </div>
            <div class="campo-ejercicio">
                <label>Repeticiones</label>
                <input type="number" name="ejercicios[${idx}][reps]" placeholder="Ej: 5" min="1" value="${datos.reps || ''}" required>
            </div>
            ${pesosHTML}
            <input type="hidden" name="ejercicios[${idx}][bloque]" value="${idBloque}">
            <input type="hidden" name="ejercicios[${idx}][rondas]" value="${rondasVal}" class="input-rondas-${idBloque}">
            <button type="button" class="fila-ejercicio-builder__btn-eliminar" onclick="this.parentElement.remove()" title="Quitar ejercicio">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
    } else {
        fila.innerHTML = `
            <div class="campo-ejercicio">
                <label>Ejercicio</label>
                <select name="ejercicios[${idx}][ejercicio_id]" required>
                    ${opcionesEj}
                </select>
            </div>
            <div class="campo-ejercicio">
                <label>Series</label>
                <input type="number" name="ejercicios[${idx}][series]" placeholder="Ej: 4 (opcional)" min="1" value="${datos.series || ''}">
            </div>
            <div class="campo-ejercicio">
                <label>Repeticiones</label>
                <input type="number" name="ejercicios[${idx}][reps]" placeholder="Ej: 15" min="1" value="${datos.reps || ''}" required>
            </div>
            <div class="campo-ejercicio">
                <label>Notas / Cargas</label>
                <input type="text" name="ejercicios[${idx}][notas]" placeholder="Notas / Variación (ej: Unbroken, Rx)" value="${datos.notas || ''}">
            </div>
            <input type="hidden" name="ejercicios[${idx}][bloque]" value="${idBloque}">
            <input type="hidden" name="ejercicios[${idx}][rondas]" value="${rondasVal}" class="input-rondas-${idBloque}">
            <button type="button" class="fila-ejercicio-builder__btn-eliminar" onclick="this.parentElement.remove()" title="Quitar ejercicio">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
    }

    lista.appendChild(fila);
}

// -------------------------------------------------------------
// CONSTRUCTOR ESTÁNDAR CON PESTAÑAS ♂ HOMBRES / ♀ MUJERES POR DÍA
// -------------------------------------------------------------
function cambiarPestanaGenero(btn, idDia, genero) {
    const card = btn.closest('.bloque-rutina');
    card.querySelectorAll('.btn-genero-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const panelH = card.querySelector('.panel-genero-builder--hombres');
    const panelM = card.querySelector('.panel-genero-builder--mujeres');

    if(genero === 'hombres') {
        if(panelH) panelH.style.display = 'block';
        if(panelM) panelM.style.display = 'none';
    } else {
        if(panelH) panelH.style.display = 'none';
        if(panelM) panelM.style.display = 'block';
    }
}

function copiarEjerciciosGenero(idDia, diaNum) {
    const listaOrigen = document.getElementById(`lista_ejercicios_${idDia}_hombres`);
    const listaDestino = document.getElementById(`lista_ejercicios_${idDia}_mujeres`);
    const vacioDestino = document.getElementById(`vacio_${idDia}_mujeres`);

    if(!listaOrigen) return;
    const filas = listaOrigen.querySelectorAll('.fila-ejercicio-builder');
    if(filas.length === 0) {
        alert('No hay ejercicios en la rutina de hombres para copiar.');
        return;
    }

    if(confirm(`¿Copiar ${filas.length} ejercicio(s) de Hombres a la rutina de Mujeres del Día ${diaNum}?`)) {
        if(vacioDestino) vacioDestino.style.display = 'none';

        filas.forEach(f => {
            const ejSelect = f.querySelector('select');
            const seriesInput = f.querySelector('input[name*="[series]"]');
            const repsInput = f.querySelector('input[name*="[reps]"]');
            const notasInput = f.querySelector('input[name*="[notas]"]');

            const datos = {
                ejercicio_id: ejSelect ? ejSelect.value : '',
                series: seriesInput ? seriesInput.value : '',
                reps: repsInput ? repsInput.value : '',
                notas: notasInput ? notasInput.value : ''
            };
            agregarFilaEstandar(`${idDia}_mujeres`, diaNum, datos);
        });

        const card = listaOrigen.closest('.bloque-rutina');
        const btnMujer = card.querySelector('.btn-genero-tab--mujeres');
        if(btnMujer) {
            cambiarPestanaGenero(btnMujer, idDia, 'mujeres');
        }
    }
}

function agregarBloqueEstandar(datosGrupo = null) {
    contadorDiasEstandar++;
    const contenedor = document.getElementById('contenedor_bloques_rutina');
    const select = document.getElementById('selector_plan');
    const opcion = select ? select.options[select.selectedIndex] : null;
    const esMusculacion = opcion ? opcion.getAttribute('data-musculacion') === '1' : false;
    const esIndividual = esRutinaIndividual();

    const idDia = `dia_${contadorDiasEstandar}`;
    const card = document.createElement('div');
    card.className = 'bloque-rutina';
    card.id = `bloque_${idDia}`;

    if(esMusculacion && !esIndividual) {
        // Musculación Grupal: Pestañas diferenciadas ♂ Hombres / ♀ Mujeres
        const idHombres = `${idDia}_hombres`;
        const idMujeres = `${idDia}_mujeres`;

        card.innerHTML = `
            <div class="bloque-rutina__header">
                <h4><i class="fa-solid fa-calendar-day"></i> Día ${contadorDiasEstandar} de Entrenamiento</h4>
                <button type="button" style="background: none; border: none; color: #ef4444; font-size: 1.3rem; font-weight: 700; cursor: pointer;" onclick="document.getElementById('bloque_${idDia}').remove()">
                    <i class="fa-solid fa-xmark"></i> Eliminar este Día
                </button>
            </div>

            <!-- Pestañas ♂ Hombres / ♀ Mujeres dentro del Día -->
            <div class="bloque-rutina__genero-tabs">
                <button type="button" class="btn-genero-tab btn-genero-tab--hombres active" onclick="cambiarPestanaGenero(this, '${idDia}', 'hombres')">
                    <i class="fa-solid fa-mars"></i> ♂ Rutina Hombres
                </button>
                <button type="button" class="btn-genero-tab btn-genero-tab--mujeres" onclick="cambiarPestanaGenero(this, '${idDia}', 'mujeres')">
                    <i class="fa-solid fa-venus"></i> ♀ Rutina Mujeres
                </button>
                <button type="button" class="btn-copiar-genero" onclick="copiarEjerciciosGenero('${idDia}', ${contadorDiasEstandar})" title="Duplicar los ejercicios de hombres en mujeres para personalizarlos">
                    <i class="fa-solid fa-copy"></i> Copiar ejercicios de Hombres a Mujeres
                </button>
            </div>

            <!-- Panel Hombres -->
            <div class="panel-genero-builder panel-genero-builder--hombres" id="panel_${idHombres}">
                <div class="bloque-rutina__ejercicios" id="lista_ejercicios_${idHombres}"></div>
                <button type="button" class="btn-agregar-ejercicio" onclick="agregarFilaEstandar('${idHombres}', ${contadorDiasEstandar})">
                    <i class="fa-solid fa-plus"></i> + Agregar Ejercicio (Hombres - Día ${contadorDiasEstandar})
                </button>
            </div>

            <!-- Panel Mujeres -->
            <div class="panel-genero-builder panel-genero-builder--mujeres" id="panel_${idMujeres}" style="display: none;">
                <div id="vacio_${idMujeres}" class="vacio-genero-builder" style="${datosGrupo && datosGrupo.mujeres && datosGrupo.mujeres.length > 0 ? 'display: none;' : ''}">
                    <i class="fa-solid fa-venus"></i>
                    <p>Aún no hay ejercicios cargados para mujeres en el Día ${contadorDiasEstandar}.</p>
                </div>
                <div class="bloque-rutina__ejercicios" id="lista_ejercicios_${idMujeres}"></div>
                <button type="button" class="btn-agregar-ejercicio" onclick="agregarFilaEstandar('${idMujeres}', ${contadorDiasEstandar})">
                    <i class="fa-solid fa-plus"></i> + Agregar Ejercicio (Mujeres - Día ${contadorDiasEstandar})
                </button>
            </div>
        `;

        contenedor.appendChild(card);

        if(datosGrupo) {
            if(datosGrupo.hombres && datosGrupo.hombres.length > 0) {
                datosGrupo.hombres.forEach(item => agregarFilaEstandar(idHombres, contadorDiasEstandar, item));
            } else {
                agregarFilaEstandar(idHombres, contadorDiasEstandar);
            }

            if(datosGrupo.mujeres && datosGrupo.mujeres.length > 0) {
                datosGrupo.mujeres.forEach(item => agregarFilaEstandar(idMujeres, contadorDiasEstandar, item));
            }
        } else {
            agregarFilaEstandar(idHombres, contadorDiasEstandar);
        }
    } else {
        // Funcional o Musculación Individual (unificada sin distinción de género)
        let rondasDia = '';
        if(!esMusculacion && datosGrupo && Array.isArray(datosGrupo) && datosGrupo.length > 0) {
            rondasDia = datosGrupo[0].rondas || '';
        }

        let parametrosRondasHTML = '';
        if(!esMusculacion) {
            parametrosRondasHTML = `
                <div class="bloque-rutina__parametros">
                    <label>Cantidad de Rondas / Vueltas (opcional):</label>
                    <input type="number" 
                           id="rondas_${idDia}" 
                           placeholder="Ej: 4 (opcional)" 
                           min="1" 
                           value="${rondasDia}"
                           oninput="actualizarRondasBloque('${idDia}', this.value)">
                </div>
            `;
        }

        card.innerHTML = `
            <div class="bloque-rutina__header">
                <h4><i class="fa-solid fa-calendar-day"></i> Día ${contadorDiasEstandar} de Entrenamiento ${esMusculacion ? '(Rutina Individual)' : ''}</h4>
                ${parametrosRondasHTML}
                <button type="button" style="background: none; border: none; color: #ef4444; font-size: 1.3rem; font-weight: 700; cursor: pointer;" onclick="document.getElementById('bloque_${idDia}').remove()">
                    <i class="fa-solid fa-xmark"></i> Eliminar este Día
                </button>
            </div>
            <div class="bloque-rutina__ejercicios" id="lista_ejercicios_${idDia}"></div>
            <button type="button" class="btn-agregar-ejercicio" onclick="agregarFilaEstandar('${idDia}', ${contadorDiasEstandar})">
                <i class="fa-solid fa-plus"></i> + Agregar Ejercicio al Día ${contadorDiasEstandar}
            </button>
        `;

        contenedor.appendChild(card);

        if(datosGrupo) {
            if(Array.isArray(datosGrupo) && datosGrupo.length > 0) {
                datosGrupo.forEach(item => agregarFilaEstandar(idDia, contadorDiasEstandar, item));
            } else if(datosGrupo.hombres || datosGrupo.mujeres) {
                const todos = [...(datosGrupo.hombres || []), ...(datosGrupo.mujeres || [])];
                if(todos.length > 0) {
                    todos.forEach(item => agregarFilaEstandar(idDia, contadorDiasEstandar, item));
                } else {
                    agregarFilaEstandar(idDia, contadorDiasEstandar);
                }
            } else {
                agregarFilaEstandar(idDia, contadorDiasEstandar);
            }
        } else {
            agregarFilaEstandar(idDia, contadorDiasEstandar);
        }
    }
}

function agregarFilaEstandar(idBloque, diaNum, datos = {}) {
    const lista = document.getElementById(`lista_ejercicios_${idBloque}`);
    if(!lista) return;

    const vacio = document.getElementById(`vacio_${idBloque}`);
    if(vacio) vacio.style.display = 'none';

    const idx = indiceEjercicio++;
    const rondasInput = document.getElementById(`rondas_${idBloque}`);
    const rondasVal = (datos.rondas !== undefined && datos.rondas !== null && datos.rondas !== '') 
        ? datos.rondas 
        : (rondasInput ? rondasInput.value : '');

    const fila = document.createElement('div');
    fila.className = 'fila-ejercicio-builder fila-ejercicio-builder--estandar';

    const opcionesEj = getOpcionesEjerciciosHTML(datos.ejercicio_id || null);

    fila.innerHTML = `
        <div class="campo-ejercicio">
            <label>Ejercicio</label>
            <select name="ejercicios[${idx}][ejercicio_id]" required>
                ${opcionesEj}
            </select>
        </div>
        <div class="campo-ejercicio">
            <label>Series</label>
            <input type="number" name="ejercicios[${idx}][series]" placeholder="Ej: 4" min="1" value="${datos.series || 4}" required>
        </div>
        <div class="campo-ejercicio">
            <label>Repeticiones</label>
            <input type="number" name="ejercicios[${idx}][reps]" placeholder="Ej: 10" min="1" value="${datos.reps || 10}" required>
        </div>
        <div class="campo-ejercicio">
            <label>Notas / Cargas (Opcional)</label>
            <input type="text" name="ejercicios[${idx}][notas]" placeholder="Ej: 50 kg, al fallo, tempo..." value="${datos.notas || ''}">
        </div>
        <input type="hidden" name="ejercicios[${idx}][bloque]" value="${idBloque}">
        <input type="hidden" name="ejercicios[${idx}][dia]" value="${diaNum}">
        <input type="hidden" name="ejercicios[${idx}][rondas]" value="${rondasVal}" class="input-rondas-${idBloque}">
        <button type="button" class="fila-ejercicio-builder__btn-eliminar" onclick="eliminarFilaEstandar(this, '${idBloque}')" title="Quitar ejercicio">
            <i class="fa-solid fa-trash-can"></i>
        </button>
    `;

    lista.appendChild(fila);
}

function eliminarFilaEstandar(btn, idBloque) {
    const fila = btn.parentElement;
    const lista = fila.parentElement;
    fila.remove();
    if(lista && lista.children.length === 0) {
        const vacio = document.getElementById(`vacio_${idBloque}`);
        if(vacio) vacio.style.display = 'block';
    }
}

function onCambioTipoDestinatario(esGrupal) {
    const select = document.getElementById('selector_plan');
    if(!select || !select.value) return;
    const opcion = select.options[select.selectedIndex];
    const esMusculacion = opcion ? opcion.getAttribute('data-musculacion') === '1' : false;
    const esCrossfit = opcion ? opcion.getAttribute('data-crossfit') === '1' : false;

    if(esMusculacion) {
        const contenedor = document.getElementById('contenedor_bloques_rutina');
        const filas = contenedor ? contenedor.querySelectorAll('.fila-ejercicio-builder') : [];
        if(filas.length <= 1 || confirm('Al cambiar entre rutina grupal e individual en Musculación se reorganizará la estructura de los días. ¿Deseas continuar?')) {
            contenedor.innerHTML = '';
            indiceEjercicio = 0;
            contadorDiasEstandar = 0;
            agregarBloqueEstandar();
        }
    } else if(esCrossfit) {
        const filasFuerza = document.querySelectorAll('#lista_ejercicios_fuerza .fila-ejercicio-builder');
        filasFuerza.forEach(f => {
            const inputH = f.querySelector('input[name*="[peso_hombres]"]');
            const colH = inputH ? inputH.parentElement : null;
            const labelH = colH ? colH.querySelector('label') : null;
            const colM = f.querySelector('input[name*="[peso_mujeres]"]')?.parentElement;
            if(!esGrupal) {
                if(labelH) labelH.textContent = 'Peso / Carga Sugerida (Rx)';
                if(colH) colH.style.flex = '2';
                if(colM) colM.style.display = 'none';
            } else {
                if(labelH) labelH.textContent = 'Peso H (Rx)';
                if(colH) colH.style.flex = '';
                if(colM) colM.style.display = 'flex';
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('selector_plan');
    if(select && select.value) {
        const opcion = select.options[select.selectedIndex];
        const esCrossfit = opcion.getAttribute('data-crossfit') === '1';
        const esMusculacion = opcion.getAttribute('data-musculacion') === '1';

        const seccionWod = document.getElementById('seccion_parametros_wod');
        const btnAgregarDia = document.getElementById('btn_agregar_dia_contenedor');

        if(esCrossfit) {
            seccionWod.style.display = 'block';
            btnAgregarDia.style.display = 'none';
            construirBloquesCrossfit();
        } else {
            seccionWod.style.display = 'none';
            btnAgregarDia.style.display = 'block';

            const esIndividual = esRutinaIndividual();

            if(esMusculacion && !esIndividual) {
                // Agrupar ejercicios precargados por día y género (Musculación Grupal)
                const diasMap = {};
                ejerciciosPrecargados.forEach(ej => {
                    const d = ej.dia || 1;
                    if(!diasMap[d]) diasMap[d] = { hombres: [], mujeres: [] };
                    const b = (ej.bloque || '').toLowerCase();
                    if(b.includes('mujeres') || b.includes('mujer')) {
                        diasMap[d].mujeres.push(ej);
                    } else {
                        diasMap[d].hombres.push(ej);
                    }
                });

                if(Object.keys(diasMap).length > 0) {
                    Object.keys(diasMap).forEach(d => {
                        agregarBloqueEstandar(diasMap[d]);
                    });
                } else {
                    agregarBloqueEstandar();
                }
            } else {
                // Funcional / Musculación Individual: Agrupar ejercicios por día unificados
                const diasMap = {};
                ejerciciosPrecargados.forEach(ej => {
                    const d = ej.dia || 1;
                    if(!diasMap[d]) diasMap[d] = [];
                    diasMap[d].push(ej);
                });

                if(Object.keys(diasMap).length > 0) {
                    Object.keys(diasMap).forEach(d => {
                        agregarBloqueEstandar(diasMap[d]);
                    });
                } else {
                    agregarBloqueEstandar();
                }
            }
        }
    }
});
</script>
