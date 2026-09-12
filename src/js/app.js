/**
 * ==============================================================================
 * GYM MVC - APLICACIÓN JAVASCRIPT PRINCIPAL (src/js/app.js)
 * ==============================================================================
 * Cada funcionalidad está modularizada con funciones de inicio protegidas por
 * guard clauses (verificando la existencia de los elementos DOM requeridos) para
 * que puedan convivir de forma limpia, modular y segura en todo el sistema.
 */

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    iniciarSidebar();
    iniciarReservasCliente();
    iniciarTurnosCliente();
    iniciarGrillaHorarios();
    iniciarCopiarEmail();
    iniciarAdminEntrenadores();
    iniciarAdminEjercicios();
    iniciarAdminUsuarios();
    iniciarAdminConfiguracion();
    iniciarAdminHorariosFormulario();
    iniciarAdminReservas();
    iniciarEntrenadorAsistencia();
    iniciarEntrenadorAlumnos();
    iniciarDetalleRutinaWod();
    iniciarPasswordToggle();
}

/**
 * ==============================================================================
 * 1. SIDEBAR / MENÚ DEL PANEL (DASHBOARD)
 * Vista: views/layout.php
 * ==============================================================================
 */
function iniciarSidebar() {
    const sidebar = document.getElementById('sidebar');
    const dashboard = document.getElementById('dashboard');
    const toggleBtn = document.getElementById('sidebar_toggle');
    const toggleIcon = document.getElementById('sidebar_toggle_icono');

    if (!sidebar || !toggleBtn) return;

    const esDesktop = () => window.innerWidth >= 768;

    // Restaurar estado guardado en desktop
    if (esDesktop() && localStorage.getItem('gym_sidebar_colapsada') === 'true') {
        sidebar.classList.add('sidebar--colapsada');
        if (dashboard) dashboard.classList.add('dashboard--colapsado');
    }

    toggleBtn.addEventListener('click', () => {
        if (esDesktop()) {
            // Modo Escritorio: colapsar / desplegar barra lateral
            const colapsada = sidebar.classList.toggle('sidebar--colapsada');
            if (dashboard) dashboard.classList.toggle('dashboard--colapsado', colapsada);
            localStorage.setItem('gym_sidebar_colapsada', colapsada ? 'true' : 'false');
        } else {
            // Modo Móvil: desplegar / cerrar menú superior
            const abierta = sidebar.classList.toggle('sidebar--abierto');
            if (toggleIcon) {
                if (abierta) {
                    toggleIcon.classList.remove('fa-bars');
                    toggleIcon.classList.add('fa-xmark');
                } else {
                    toggleIcon.classList.remove('fa-xmark');
                    toggleIcon.classList.add('fa-bars');
                }
            }
        }
    });

    // Cerrar menú móvil al hacer click en un enlace
    const enlaces = sidebar.querySelectorAll('.sidebar__nav a');
    enlaces.forEach(enlace => {
        enlace.addEventListener('click', () => {
            if (!esDesktop() && sidebar.classList.contains('sidebar--abierto')) {
                sidebar.classList.remove('sidebar--abierto');
                if (toggleIcon) {
                    toggleIcon.classList.remove('fa-xmark');
                    toggleIcon.classList.add('fa-bars');
                }
            }
        });
    });

    // Ajustar al redimensionar ventana
    window.addEventListener('resize', () => {
        if (esDesktop()) {
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-xmark');
                toggleIcon.classList.add('fa-bars');
            }
            const estaColapsada = localStorage.getItem('gym_sidebar_colapsada') === 'true';
            sidebar.classList.toggle('sidebar--colapsada', estaColapsada);
            if (dashboard) dashboard.classList.toggle('dashboard--colapsado', estaColapsada);
        } else {
            sidebar.classList.remove('sidebar--colapsada');
            if (dashboard) dashboard.classList.remove('dashboard--colapsado');
        }
    });
}

/**
 * ==============================================================================
 * 2. RESERVAS DE TURNOS Y CALENDARIO SEMANAL DEL CLIENTE
 * Vista: views/cliente/reservar.php
 * ==============================================================================
 */
function iniciarReservasCliente() {
    const carruselDias = document.getElementById('carrusel_dias');
    const contenedorTurnos = document.getElementById('contenedor_turnos');
    if (!carruselDias || !contenedorTurnos) return;

    const tabsDias = document.querySelectorAll('.tab-dia');
    const btnAnterior = document.getElementById('btn_dia_anterior');
    const btnSiguiente = document.getElementById('btn_dia_siguiente');
    const btnOtraFecha = document.getElementById('btn_otra_fecha');
    const inputFechaManual = document.getElementById('selector_fecha_manual');
    const labelFecha = document.getElementById('label_fecha_seleccionada');
    const avisoDomingo = document.getElementById('aviso_domingo');
    const contenedorAlertas = document.getElementById('contenedor_alertas');

    // Filtros dinámicos
    const barraFiltros = document.getElementById('filtros_reservas');
    const contadorResultados = document.getElementById('contador_resultados');
    const btnLimpiarFiltros = document.getElementById('btn_limpiar_filtros');
    const pillsDisciplina = document.querySelectorAll('#filtro_disciplinas .filtro-pill');
    const pillsTurno = document.querySelectorAll('#filtro_turnos .filtro-pill');

    // Modal de cancelación
    const modalCancelar = document.getElementById('modal_confirmar_cancelar');
    const modalClase = document.getElementById('modal_cancelar_clase');
    const modalFecha = document.getElementById('modal_cancelar_fecha');
    const modalHora = document.getElementById('modal_cancelar_hora');
    const btnModalCerrar = document.getElementById('btn_modal_cancelar_cerrar');
    const btnModalConfirmar = document.getElementById('btn_modal_cancelar_confirmar');

    let listaHorariosActuales = [];
    let filtroDisciplinaActual = 'todas';
    let filtroTurnoActual = 'todos';
    let reservaIdParaCancelar = null;
    let timerAlerta = null;

    const fechaMinima = carruselDias.getAttribute('data-fecha-minima') || inputFechaManual?.min || new Date().toISOString().split('T')[0];
    let fechaSeleccionada = carruselDias.getAttribute('data-fecha-inicial') || inputFechaManual?.value || fechaMinima;

    // Eventos de selección de filtros
    pillsDisciplina.forEach(pill => {
        pill.addEventListener('click', function() {
            pillsDisciplina.forEach(p => p.classList.remove('activo'));
            this.classList.add('activo');
            filtroDisciplinaActual = this.getAttribute('data-disciplina');
            aplicarFiltros();
        });
    });

    pillsTurno.forEach(pill => {
        pill.addEventListener('click', function() {
            pillsTurno.forEach(p => p.classList.remove('activo'));
            this.classList.add('activo');
            filtroTurnoActual = this.getAttribute('data-turno');
            aplicarFiltros();
        });
    });

    if(btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', restablecerFiltros);
    }

    function restablecerFiltros() {
        filtroDisciplinaActual = 'todas';
        filtroTurnoActual = 'todos';

        pillsDisciplina.forEach(p => {
            if(p.getAttribute('data-disciplina') === 'todas') p.classList.add('activo');
            else p.classList.remove('activo');
        });

        pillsTurno.forEach(p => {
            if(p.getAttribute('data-turno') === 'todos') p.classList.add('activo');
            else p.classList.remove('activo');
        });

        aplicarFiltros();
    }

    // Modal de cancelación
    function abrirModalCancelar(reservaId, clase, fecha, horario) {
        reservaIdParaCancelar = reservaId;
        if(modalClase) modalClase.textContent = clase || 'Clase';
        if(modalFecha) modalFecha.textContent = formatearFechaBonita(fecha);
        if(modalHora) modalHora.textContent = horario || '';
        if(btnModalConfirmar) {
            btnModalConfirmar.disabled = false;
            btnModalConfirmar.innerHTML = `<i class="fa-solid fa-trash-can"></i> Sí, cancelar`;
        }
        if(modalCancelar) {
            modalCancelar.classList.add('activo');
            modalCancelar.setAttribute('aria-hidden', 'false');
        }
    }

    function cerrarModalCancelar() {
        if(modalCancelar) {
            modalCancelar.classList.remove('activo');
            modalCancelar.setAttribute('aria-hidden', 'true');
        }
        reservaIdParaCancelar = null;
    }

    if(btnModalCerrar) {
        btnModalCerrar.addEventListener('click', cerrarModalCancelar);
    }

    if(modalCancelar) {
        modalCancelar.addEventListener('click', function(e) {
            if(e.target === modalCancelar) {
                cerrarModalCancelar();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape' && modalCancelar && modalCancelar.classList.contains('activo')) {
            cerrarModalCancelar();
        }
    });

    if(btnModalConfirmar) {
        btnModalConfirmar.addEventListener('click', async function() {
            if(!reservaIdParaCancelar) return;

            btnModalConfirmar.disabled = true;
            btnModalConfirmar.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Cancelando...`;

            try {
                const respuesta = await fetch('/api/reservas/cancelar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reserva_id: reservaIdParaCancelar
                    })
                });

                const resultado = await respuesta.json();

                cerrarModalCancelar();

                if(resultado.tipo === 'exito') {
                    mostrarAlerta('info', resultado.mensaje || 'Tu reserva fue cancelada y el cupo ha sido liberado.', 'Reserva Cancelada');
                } else {
                    mostrarAlerta('error', resultado.mensaje || 'No se pudo cancelar la reserva.');
                }

                await cargarHorarios(fechaSeleccionada, false);
            } catch(error) {
                console.error(error);
                cerrarModalCancelar();
                mostrarAlerta('error', 'Ocurrió un error al cancelar.');
                await cargarHorarios(fechaSeleccionada, false);
            }
        });
    }

    // Inicializar estado de botones y cargar turnos
    actualizarEstadoFlechas();
    cargarHorarios(fechaSeleccionada);

    // Selección por Tabs
    tabsDias.forEach(tab => {
        tab.addEventListener('click', function() {
            const fecha = this.getAttribute('data-fecha');
            seleccionarFecha(fecha, true);
        });
    });

    // Desplazamiento con rueda de ratón sobre el carrusel horizontal
    carruselDias.addEventListener('wheel', function(e) {
        if(e.deltaY !== 0) {
            e.preventDefault();
            carruselDias.scrollLeft += e.deltaY;
        }
    }, { passive: false });

    // Flechas de navegación día por día (saltando domingos)
    if(btnAnterior) {
        btnAnterior.addEventListener('click', function() {
            const fechaAnt = calcularAnteriorDiaHabil(fechaSeleccionada);
            if(fechaAnt >= fechaMinima) {
                seleccionarFecha(fechaAnt, true);
            }
        });
    }

    if(btnSiguiente) {
        btnSiguiente.addEventListener('click', function() {
            const fechaSig = calcularSiguienteDiaHabil(fechaSeleccionada);
            seleccionarFecha(fechaSig, true);
        });
    }

    // Disparador del selector nativo de fechas al pulsar "Otra fecha"
    function dispararSelectorFecha() {
        if(!inputFechaManual) return;
        if(typeof inputFechaManual.showPicker === 'function') {
            try {
                inputFechaManual.showPicker();
            } catch(e) {
                inputFechaManual.focus();
            }
        } else {
            inputFechaManual.focus();
        }
    }

    if(btnOtraFecha) {
        btnOtraFecha.addEventListener('click', function(e) {
            if(e.target !== inputFechaManual) {
                dispararSelectorFecha();
            }
        });

        btnOtraFecha.addEventListener('keydown', function(e) {
            if(e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                dispararSelectorFecha();
            }
        });
    }

    if(inputFechaManual) {
        inputFechaManual.addEventListener('click', function(e) {
            if(typeof inputFechaManual.showPicker === 'function') {
                try {
                    inputFechaManual.showPicker();
                } catch(err) {}
            }
        });

        inputFechaManual.addEventListener('change', function() {
            if(!this.value) return;
            seleccionarFecha(this.value, true);
        });
    }

    function calcularSiguienteDiaHabil(fechaStr) {
        const partes = fechaStr.split('-');
        const fecha = new Date(partes[0], partes[1] - 1, partes[2]);
        fecha.setDate(fecha.getDate() + 1);
        if(fecha.getDay() === 0) {
            fecha.setDate(fecha.getDate() + 1);
        }
        return formatearFechaISO(fecha);
    }

    function calcularAnteriorDiaHabil(fechaStr) {
        const partes = fechaStr.split('-');
        const fecha = new Date(partes[0], partes[1] - 1, partes[2]);
        fecha.setDate(fecha.getDate() - 1);
        if(fecha.getDay() === 0) {
            fecha.setDate(fecha.getDate() - 1);
        }
        return formatearFechaISO(fecha);
    }

    function formatearFechaISO(fechaObj) {
        const anio = fechaObj.getFullYear();
        const mes = String(fechaObj.getMonth() + 1).padStart(2, '0');
        const dia = String(fechaObj.getDate()).padStart(2, '0');
        return `${anio}-${mes}-${dia}`;
    }

    function formatearFechaBonita(fechaStr) {
        const partes = fechaStr.split('-');
        const fechaObj = new Date(partes[0], partes[1] - 1, partes[2]);
        const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return `${dias[fechaObj.getDay()]} ${fechaObj.getDate()} ${meses[fechaObj.getMonth()]}`;
    }

    function actualizarEstadoFlechas() {
        if(!btnAnterior) return;
        const fechaAnt = calcularAnteriorDiaHabil(fechaSeleccionada);
        if(fechaAnt < fechaMinima) {
            btnAnterior.disabled = true;
            btnAnterior.classList.add('disabled');
        } else {
            btnAnterior.disabled = false;
            btnAnterior.classList.remove('disabled');
        }
    }

    function seleccionarFecha(fecha, recargar = true) {
        fechaSeleccionada = fecha;
        actualizarEstadoFlechas();

        let tabEncontrada = null;
        tabsDias.forEach(tab => {
            if(tab.getAttribute('data-fecha') === fecha) {
                tab.classList.add('activo');
                tab.setAttribute('aria-selected', 'true');
                tabEncontrada = tab;
            } else {
                tab.classList.remove('activo');
                tab.setAttribute('aria-selected', 'false');
            }
        });

        if(tabEncontrada) {
            if(labelFecha) labelFecha.textContent = 'Otra fecha';
            if(btnOtraFecha) btnOtraFecha.classList.remove('activo');
            tabEncontrada.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        } else {
            if(labelFecha) {
                labelFecha.textContent = formatearFechaBonita(fecha);
            }
            if(btnOtraFecha) {
                btnOtraFecha.classList.add('activo');
            }
        }

        if(recargar) {
            cargarHorarios(fecha);
        }
    }

    async function cargarHorarios(fecha, limpiarAlertas = true, recienReservadoId = null) {
        if(limpiarAlertas && contenedorAlertas) {
            contenedorAlertas.innerHTML = '';
        }
        
        // Verificar si es domingo
        const partes = fecha.split('-');
        const fechaObj = new Date(partes[0], partes[1] - 1, partes[2]);
        if(fechaObj.getDay() === 0) {
            if(avisoDomingo) avisoDomingo.style.display = 'block';
            if(barraFiltros) barraFiltros.style.display = 'none';
            contenedorTurnos.innerHTML = `
                <div class="turnos-vacio">
                    <i class="fa-regular fa-calendar-xmark" style="color: #ef4444;"></i>
                    <h3>Gimnasio Cerrado</h3>
                    <p>Los domingos no se dictan clases. Por favor seleccioná otro día.</p>
                </div>
            `;
            return;
        }

        if(avisoDomingo) avisoDomingo.style.display = 'none';
        if(barraFiltros) barraFiltros.style.display = 'none';
        contenedorTurnos.innerHTML = `
            <div class="turnos-skeleton-grid">
                <div class="card-reserva-skeleton">
                    <div class="skeleton-line skeleton-line--header"></div>
                    <div class="skeleton-line skeleton-line--title"></div>
                    <div class="skeleton-line skeleton-line--text"></div>
                    <div class="skeleton-line skeleton-line--bar"></div>
                    <div class="skeleton-line skeleton-line--button"></div>
                </div>
                <div class="card-reserva-skeleton">
                    <div class="skeleton-line skeleton-line--header"></div>
                    <div class="skeleton-line skeleton-line--title"></div>
                    <div class="skeleton-line skeleton-line--text"></div>
                    <div class="skeleton-line skeleton-line--bar"></div>
                    <div class="skeleton-line skeleton-line--button"></div>
                </div>
                <div class="card-reserva-skeleton">
                    <div class="skeleton-line skeleton-line--header"></div>
                    <div class="skeleton-line skeleton-line--title"></div>
                    <div class="skeleton-line skeleton-line--text"></div>
                    <div class="skeleton-line skeleton-line--bar"></div>
                    <div class="skeleton-line skeleton-line--button"></div>
                </div>
            </div>
        `;

        try {
            const respuesta = await fetch(`/api/horarios?fecha=${fecha}`);
            const horarios = await respuesta.json();

            listaHorariosActuales = Array.isArray(horarios) ? horarios : [];
            aplicarFiltros(recienReservadoId);
        } catch (error) {
            console.error(error);
            if(barraFiltros) barraFiltros.style.display = 'none';
            contenedorTurnos.innerHTML = `
                <div class="alerta error">Hubo un error al cargar los turnos. Por favor intentá nuevamente.</div>
            `;
        }
    }

    function aplicarFiltros(recienReservadoId = null) {
        if(!listaHorariosActuales || listaHorariosActuales.length === 0) {
            if(barraFiltros) barraFiltros.style.display = 'none';
            contenedorTurnos.innerHTML = `
                <div class="turnos-vacio">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <h3>No hay clases programadas para este día</h3>
                    <p>Probá seleccionando otra fecha en el calendario.</p>
                </div>
            `;
            return;
        }

        if(barraFiltros) barraFiltros.style.display = 'flex';

        // Filtrar en memoria
        const filtrados = listaHorariosActuales.filter(h => {
            // Filtro por disciplina
            if(filtroDisciplinaActual !== 'todas') {
                const nombrePlan = (h.plan_nombre || '').toLowerCase().trim();
                if(nombrePlan !== filtroDisciplinaActual) {
                    return false;
                }
            }

            // Filtro por franja horaria
            if(filtroTurnoActual !== 'todos') {
                const horaInicio = (h.hora_inicio || '').substring(0, 5);
                const horaFin = (h.hora_fin || '').substring(0, 5) || horaInicio;

                if(filtroTurnoActual === 'manana') {
                    // Mañana (< 12 hs): Clases que comienzan antes de las 12:00 hs
                    if(horaInicio >= '12:00') return false;
                } else if(filtroTurnoActual === 'tarde') {
                    // Tarde (12 a 18 hs): Clases que comienzan en la tarde, o bloques continuos que abarcan la tarde
                    const solapaTarde = (horaInicio < '18:00' && horaFin > '12:00') || (horaInicio >= '12:00' && horaInicio < '18:00');
                    if(!solapaTarde) return false;
                } else if(filtroTurnoActual === 'noche') {
                    // Noche (> 18 hs): Clases que comienzan a las 18:00 hs o posterior, o bloques continuos que abarcan la noche
                    const solapaNoche = (horaFin > '18:00') || (horaInicio >= '18:00');
                    if(!solapaNoche) return false;
                }
            }

            return true;
        });

        // Mostrar / ocultar botón limpiar
        const hayFiltrosActivos = (filtroDisciplinaActual !== 'todas' || filtroTurnoActual !== 'todos');
        if(btnLimpiarFiltros) {
            btnLimpiarFiltros.style.display = hayFiltrosActivos ? 'inline-flex' : 'none';
        }

        // Contador de resultados
        if(contadorResultados) {
            const total = filtrados.length;
            const conCupo = filtrados.filter(h => !h.agotado && !h.pasado).length;
            if(total === 0) {
                contadorResultados.innerHTML = `No se encontraron clases con los filtros aplicados`;
            } else if(total === 1) {
                contadorResultados.innerHTML = `Mostrando <strong>1</strong> clase (${conCupo} disponible)`;
            } else {
                contadorResultados.innerHTML = `Mostrando <strong>${total}</strong> clases (${conCupo} con cupo disponible)`;
            }
        }

        if(filtrados.length === 0) {
            contenedorTurnos.innerHTML = `
                <div class="turnos-vacio">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                    <h3>No hay clases para los filtros seleccionados</h3>
                    <p>Probá cambiando la disciplina o la franja horaria para ver más opciones.</p>
                    <button type="button" class="btn-limpiar-filtros" id="btn_restablecer_filtros" style="margin-top: 1.5rem; padding: 0.8rem 1.6rem; border: 1px solid #cbd5e1; border-radius: 2rem;">
                        <i class="fa-solid fa-rotate-left"></i> Restablecer todos los filtros
                    </button>
                </div>
            `;
            document.getElementById('btn_restablecer_filtros')?.addEventListener('click', restablecerFiltros);
            return;
        }

        renderizarHorarios(filtrados, fechaSeleccionada, recienReservadoId);
    }

    function renderizarHorarios(horarios, fecha, recienReservadoId = null) {
        let html = `<div class="grid-turnos">`;

        horarios.forEach((h, index) => {
            const yaReservado = h.ya_reservado;
            const esPasado = h.pasado;
            const agotado = h.agotado && !yaReservado;
            const esIlimitado = Boolean(h.ilimitado || h.cupo_total === null);

            const cupoTotal = h.cupo_total !== null && h.cupo_total !== undefined ? parseInt(h.cupo_total, 10) : null;
            const cupoDisponible = h.cupo_disponible !== null && h.cupo_disponible !== undefined ? parseInt(h.cupo_disponible, 10) : null;
            const ocupados = (!esIlimitado && cupoTotal !== null && cupoDisponible !== null) 
                ? Math.max(0, cupoTotal - cupoDisponible) 
                : 0;
            const porcentajeOcupado = (!esIlimitado && cupoTotal && cupoTotal > 0) 
                ? Math.min(100, Math.round((ocupados / cupoTotal) * 100)) 
                : 0;

            const quedanPocos = !esIlimitado && !agotado && !esPasado && !yaReservado && (cupoDisponible !== null && cupoDisponible <= 3 && cupoDisponible > 0);

            let cardClase = 'card-reserva';
            if(recienReservadoId && (parseInt(h.id, 10) === parseInt(recienReservadoId, 10) || (h.reserva_id && parseInt(h.reserva_id, 10) === parseInt(recienReservadoId, 10)))) {
                cardClase += ' card-reserva--recien-reservado';
            }
            if(esPasado) {
                cardClase += ' card-reserva--pasado';
            } else if(yaReservado) {
                cardClase += ' card-reserva--reservado';
            } else if(agotado) {
                cardClase += ' card-reserva--agotado';
            } else if(quedanPocos) {
                cardClase += ' card-reserva--ultimos';
            }

            const delayAnimacion = (Math.min(index * 0.04, 0.4)).toFixed(2);

            let badgeHtml = '';
            if(esPasado) {
                if(yaReservado) {
                    badgeHtml = `<span class="card-reserva__badge card-reserva__badge--pasado"><i class="fa-solid fa-circle-check"></i> Reservaste (Finalizado)</span>`;
                } else {
                    badgeHtml = `<span class="card-reserva__badge card-reserva__badge--pasado"><i class="fa-solid fa-clock-rotate-left"></i> Turno Pasado</span>`;
                }
            } else if(yaReservado) {
                badgeHtml = `<span class="card-reserva__badge card-reserva__badge--reservado"><i class="fa-solid fa-circle-check"></i> Reservado</span>`;
            } else if(agotado) {
                badgeHtml = `<span class="card-reserva__badge card-reserva__badge--agotado"><i class="fa-solid fa-ban"></i> Sin cupo</span>`;
            } else if(quedanPocos) {
                badgeHtml = `<span class="card-reserva__badge card-reserva__badge--ultimos"><i class="fa-solid fa-fire"></i> ¡Último${cupoDisponible === 1 ? '' : 's'} ${cupoDisponible} lugar${cupoDisponible === 1 ? '' : 'es'}!</span>`;
            } else if(esIlimitado) {
                badgeHtml = `<span class="card-reserva__badge card-reserva__badge--ilimitado"><i class="fa-solid fa-infinity"></i> Cupo Libre</span>`;
            } else {
                badgeHtml = `<span class="card-reserva__badge card-reserva__badge--disponible"><i class="fa-solid fa-users"></i> ${cupoDisponible} disponibles</span>`;
            }

            let cupoVisualHtml = '';
            if(esIlimitado) {
                cupoVisualHtml = `
                    <div class="card-reserva__cupo card-reserva__cupo--ilimitado">
                        <div class="card-reserva__cupo-info">
                            <span class="card-reserva__cupo-label">
                                <i class="fa-solid fa-infinity"></i> Capacidad: <strong>Ilimitada</strong>
                            </span>
                            <span class="card-reserva__cupo-valor">
                                <i class="fa-solid fa-door-open"></i> Sala Libre
                            </span>
                        </div>
                    </div>
                `;
            } else {
                let progresoClase = 'barra-cupo__progreso--normal';
                if(agotado || porcentajeOcupado >= 100) {
                    progresoClase = 'barra-cupo__progreso--lleno';
                } else if(porcentajeOcupado >= 65 || quedanPocos) {
                    progresoClase = 'barra-cupo__progreso--alerta';
                }

                let valorTexto = '';
                let valorClase = '';
                if(agotado || (cupoDisponible !== null && cupoDisponible <= 0)) {
                    valorTexto = '100% completo';
                    valorClase = 'card-reserva__cupo-valor--agotado';
                } else if(quedanPocos) {
                    valorTexto = `¡Quedan ${cupoDisponible} libre${cupoDisponible === 1 ? '' : 's'}!`;
                    valorClase = 'card-reserva__cupo-valor--alerta';
                } else {
                    valorTexto = `${cupoDisponible} disponibles`;
                }

                cupoVisualHtml = `
                    <div class="card-reserva__cupo">
                        <div class="card-reserva__cupo-info">
                            <span class="card-reserva__cupo-label">
                                <i class="fa-solid fa-users"></i> Ocupación: <strong>${ocupados}/${cupoTotal}</strong>
                            </span>
                            <span class="card-reserva__cupo-valor ${valorClase}">
                                ${valorTexto}
                            </span>
                        </div>
                        <div class="barra-cupo" title="Ocupación: ${porcentajeOcupado}% (${ocupados} de ${cupoTotal} lugares)">
                            <div class="barra-cupo__progreso ${progresoClase}" style="width: ${porcentajeOcupado}%;"></div>
                        </div>
                    </div>
                `;
            }

            html += `
                <article class="${cardClase}" style="animation-delay: ${delayAnimacion}s;">
                    <div class="card-reserva__header">
                        <span class="card-reserva__hora ${yaReservado ? 'card-reserva__hora--reservado' : ''}">
                            <i class="fa-regular fa-clock"></i> ${h.hora_inicio} - ${h.hora_fin} hs
                        </span>
                        ${badgeHtml}
                    </div>

                    <h3 class="card-reserva__titulo">${h.plan_nombre}</h3>
                    
                    ${h.descripcion ? `<div style="color: #64748b; font-size: 1.35rem; margin: -0.4rem 0 1.2rem 0; line-height: 1.4; display: flex; align-items: baseline; gap: 0.6rem;"><i class="fa-solid fa-circle-info" style="color: #0ea5e9; font-size: 1.25rem; flex-shrink: 0;"></i><span>${h.descripcion}</span></div>` : ''}

                    <div class="card-reserva__detalles">
                        <div><i class="fa-solid fa-user-tie" style="color: #149b2b;"></i> Prof. <strong>${h.entrenador}</strong></div>
                        ${cupoVisualHtml}
                    </div>

                    <div class="card-reserva__footer">
                        ${esPasado 
                            ? (yaReservado 
                                ? `<button type="button" disabled class="btn-turno-pasado">
                                       <i class="fa-solid fa-check"></i> Turno finalizado
                                   </button>`
                                : `<button type="button" disabled class="btn-turno-pasado">
                                       <i class="fa-solid fa-ban"></i> No disponible (Horario pasado)
                                   </button>`
                              )
                            : (yaReservado 
                                ? `<button type="button" 
                                           class="btn-cancelar-turno" 
                                           data-reserva-id="${h.reserva_id}"
                                           data-clase="${h.plan_nombre}"
                                           data-horario="${h.hora_inicio} - ${h.hora_fin} hs"
                                           data-fecha="${fecha}">
                                       <i class="fa-solid fa-xmark"></i> Cancelar mi reserva
                                   </button>`
                                : (agotado
                                    ? `<button type="button" disabled class="btn-sin-cupo">
                                         Sin cupo disponible
                                       </button>`
                                    : `<button type="button" 
                                               class="btn-reservar-turno boton" 
                                               data-horario-id="${h.id}"
                                               data-fecha="${fecha}"
                                               data-clase="${h.plan_nombre}">
                                         <i class="fa-solid fa-calendar-check"></i> Reservar Lugar
                                       </button>`
                                  )
                              )
                        }
                    </div>
                </article>
            `;
        });

        html += `</div>`;
        contenedorTurnos.innerHTML = html;

        asignarEventosBotones();
    }

    function asignarEventosBotones() {
        // Reservar
        document.querySelectorAll('.btn-reservar-turno').forEach(btn => {
            btn.addEventListener('click', async function() {
                const horarioId = parseInt(this.getAttribute('data-horario-id'), 10);
                const fecha = this.getAttribute('data-fecha');
                const claseNombre = this.getAttribute('data-clase');

                this.disabled = true;
                this.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Reservando...`;

                try {
                    const respuesta = await fetch('/api/reservas', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            horario_id: horarioId,
                            fecha: fecha
                        })
                    });

                    const resultado = await respuesta.json();

                    if(resultado.tipo === 'exito') {
                        this.innerHTML = `<i class="fa-solid fa-check"></i> ¡Reservado!`;
                        mostrarAlerta('exito', resultado.mensaje || `¡Turno reservado con éxito para ${claseNombre}!`, '¡Reserva confirmada!');
                    } else {
                        mostrarAlerta('error', resultado.mensaje);
                    }
                    
                    // Recargar turnos destacando el turno recién reservado
                    await cargarHorarios(fecha, false, horarioId);
                } catch (error) {
                    console.error(error);
                    mostrarAlerta('error', 'Ocurrió un error al procesar la reserva.');
                    await cargarHorarios(fecha, false);
                }
            });
        });

        // Cancelar (abre modal moderno sin bloquear la UI)
        document.querySelectorAll('.btn-cancelar-turno').forEach(btn => {
            btn.addEventListener('click', function() {
                const reservaId = this.getAttribute('data-reserva-id');
                const clase = this.getAttribute('data-clase');
                const fecha = this.getAttribute('data-fecha');
                const horario = this.getAttribute('data-horario');

                abrirModalCancelar(reservaId, clase, fecha, horario);
            });
        });
    }

    function mostrarAlerta(tipo, mensaje, titulo = '') {
        if(!contenedorAlertas) return;
        if(timerAlerta) {
            clearTimeout(timerAlerta);
            timerAlerta = null;
        }

        const esExito = tipo === 'exito';
        const esInfo = tipo === 'info';
        
        let tituloFinal = titulo;
        if(!tituloFinal) {
            tituloFinal = esExito ? '¡Reserva confirmada!' : (esInfo ? 'Información' : 'Atención');
        }

        let icono = 'fa-solid fa-triangle-exclamation';
        if(esExito) {
            icono = 'fa-solid fa-circle-check';
        } else if(esInfo) {
            icono = 'fa-solid fa-circle-info';
        }

        contenedorAlertas.innerHTML = `
            <div class="alerta ${tipo} alerta-reserva" id="alerta_activa">
                <div class="alerta-reserva__info">
                    <i class="${icono}"></i>
                    <div>
                        <strong>${tituloFinal}</strong>
                        <span>${mensaje}</span>
                    </div>
                </div>
                <div class="alerta-reserva__acciones">
                    ${esExito ? `
                        <a href="/cliente/turnos" class="btn-mis-turnos">
                            <i class="fa-solid fa-list-check"></i> Mis Turnos
                        </a>
                    ` : ''}
                    <button type="button" class="btn-cerrar-alerta" id="btn_cerrar_alerta_banner" title="Cerrar">&times;</button>
                </div>
                <div class="alerta-reserva__timer"></div>
            </div>
        `;
        
        document.getElementById('btn_cerrar_alerta_banner')?.addEventListener('click', cerrarAlertaAnimada);

        window.scrollTo({ top: Math.max(0, contenedorAlertas.offsetTop - 80), behavior: 'smooth' });

        timerAlerta = setTimeout(() => {
            cerrarAlertaAnimada();
        }, 7000);
    }

    function cerrarAlertaAnimada() {
        if(timerAlerta) {
            clearTimeout(timerAlerta);
            timerAlerta = null;
        }
        const alerta = document.getElementById('alerta_activa');
        if(!alerta) {
            if(contenedorAlertas) contenedorAlertas.innerHTML = '';
            return;
        }
        alerta.classList.add('alerta-reserva--saliendo');
        setTimeout(() => {
            if(contenedorAlertas) contenedorAlertas.innerHTML = '';
        }, 240);
    }
}

/**
 * ==============================================================================
 * 3. MIS TURNOS Y ASISTENCIAS DEL CLIENTE
 * Vista: views/cliente/turnos.php
 * ==============================================================================
 */
function iniciarTurnosCliente() {
    const contenedorAlertas = document.getElementById('alerta_cancelacion');
    const modalCancelar = document.getElementById('modal_confirmar_cancelar');
    const btnsCancelar = document.querySelectorAll('.btn-cancelar-reserva');
    if (!contenedorAlertas && !modalCancelar && btnsCancelar.length === 0) return;

    const modalClase = document.getElementById('modal_cancelar_clase');
    const modalFecha = document.getElementById('modal_cancelar_fecha');
    const modalHora = document.getElementById('modal_cancelar_hora');
    const btnModalCerrar = document.getElementById('btn_modal_cancelar_cerrar');
    const btnModalConfirmar = document.getElementById('btn_modal_cancelar_confirmar');

    let reservaIdParaCancelar = null;
    let timerAlerta = null;

    function abrirModalCancelar(reservaId, clase, fecha, horario) {
        reservaIdParaCancelar = reservaId;
        if(modalClase) modalClase.textContent = clase || 'Clase';
        if(modalFecha) modalFecha.textContent = fecha || '';
        if(modalHora) modalHora.textContent = horario || '';
        if(btnModalConfirmar) {
            btnModalConfirmar.disabled = false;
            btnModalConfirmar.innerHTML = `<i class="fa-solid fa-trash-can"></i> Sí, cancelar`;
        }
        if(modalCancelar) {
            modalCancelar.classList.add('activo');
            modalCancelar.setAttribute('aria-hidden', 'false');
        }
    }

    function cerrarModalCancelar() {
        if(modalCancelar) {
            modalCancelar.classList.remove('activo');
            modalCancelar.setAttribute('aria-hidden', 'true');
        }
        reservaIdParaCancelar = null;
    }

    if(btnModalCerrar) {
        btnModalCerrar.addEventListener('click', cerrarModalCancelar);
    }

    if(modalCancelar) {
        modalCancelar.addEventListener('click', function(e) {
            if(e.target === modalCancelar) {
                cerrarModalCancelar();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if(e.key === 'Escape' && modalCancelar && modalCancelar.classList.contains('activo')) {
            cerrarModalCancelar();
        }
    });

    btnsCancelar.forEach(btn => {
        btn.addEventListener('click', function() {
            const reservaId = this.getAttribute('data-reserva-id');
            const clase = this.getAttribute('data-clase');
            const fecha = this.getAttribute('data-fecha');
            const horario = this.getAttribute('data-horario');

            abrirModalCancelar(reservaId, clase, fecha, horario);
        });
    });

    if(btnModalConfirmar) {
        btnModalConfirmar.addEventListener('click', async function() {
            if(!reservaIdParaCancelar) return;

            const idCancelado = reservaIdParaCancelar;
            btnModalConfirmar.disabled = true;
            btnModalConfirmar.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Cancelando...`;

            try {
                const respuesta = await fetch('/api/reservas/cancelar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reserva_id: idCancelado })
                });

                const resultado = await respuesta.json();

                cerrarModalCancelar();

                if(resultado.tipo === 'exito') {
                    // Mostrar banner con barra de progreso inmediatamente
                    mostrarAlerta('info', resultado.mensaje || 'Tu reserva fue cancelada y el cupo ha sido liberado.', 'Reserva Cancelada');

                    // Remover tarjeta con transición suave
                    const boton = document.querySelector(`.btn-cancelar-reserva[data-reserva-id="${idCancelado}"]`);
                    const card = boton ? boton.closest('.card-turno-futuro') : null;

                    if(card) {
                        card.style.transition = 'all 0.35s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();

                            // Actualizar métrica de "Próximos Turnos"
                            const contadorMetrica = document.querySelector('.metrica-card__valor');
                            if(contadorMetrica) {
                                const val = parseInt(contadorMetrica.textContent, 10);
                                if(!isNaN(val) && val > 0) contadorMetrica.textContent = val - 1;
                            }

                            // Si no quedan más turnos futuros, mostrar estado vacío
                            const gridTurnos = document.querySelector('.turnos-seccion .grid-turnos');
                            if(gridTurnos && gridTurnos.querySelectorAll('.card-turno-futuro').length === 0) {
                                const seccion = gridTurnos.closest('.turnos-seccion');
                                if(seccion) {
                                    gridTurnos.remove();
                                    const vacio = document.createElement('div');
                                    vacio.className = 'turnos-vacio';
                                    vacio.innerHTML = `
                                        <i class="fa-regular fa-calendar-xmark"></i>
                                        <h4>No tenés clases reservadas próximamente</h4>
                                        <p>Asegurá tu lugar en la clase que prefieras.</p>
                                        <a href="/cliente/reservar" class="boton">+ Reservar Turno</a>
                                    `;
                                    seccion.appendChild(vacio);
                                }
                            }
                        }, 350);
                    }
                } else {
                    mostrarAlerta('error', resultado.mensaje || 'Error al cancelar la reserva');
                }
            } catch (error) {
                console.error(error);
                cerrarModalCancelar();
                mostrarAlerta('error', 'Ocurrió un error de conexión al intentar cancelar.');
            }
        });
    }

    function mostrarAlerta(tipo, mensaje, titulo = '') {
        const contenedor = document.getElementById('alerta_cancelacion');
        if(!contenedor) return;
        if(timerAlerta) {
            clearTimeout(timerAlerta);
            timerAlerta = null;
        }

        const esExito = tipo === 'exito';
        const esInfo = tipo === 'info';
        
        let tituloFinal = titulo;
        if(!tituloFinal) {
            tituloFinal = esExito ? '¡Reserva confirmada!' : (esInfo ? 'Información' : 'Atención');
        }

        let icono = 'fa-solid fa-triangle-exclamation';
        if(esExito) {
            icono = 'fa-solid fa-circle-check';
        } else if(esInfo) {
            icono = 'fa-solid fa-circle-info';
        }

        contenedor.innerHTML = `
            <div class="alerta ${tipo} alerta-reserva" id="alerta_activa">
                <div class="alerta-reserva__info">
                    <i class="${icono}"></i>
                    <div>
                        <strong>${tituloFinal}</strong>
                        <span>${mensaje}</span>
                    </div>
                </div>
                <div class="alerta-reserva__acciones">
                    <button type="button" class="btn-cerrar-alerta" id="btn_cerrar_alerta_banner" title="Cerrar">&times;</button>
                </div>
                <div class="alerta-reserva__timer"></div>
            </div>
        `;
        
        document.getElementById('btn_cerrar_alerta_banner')?.addEventListener('click', cerrarAlertaAnimada);

        const yPos = contenedor.getBoundingClientRect().top + window.pageYOffset - 80;
        window.scrollTo({ top: Math.max(0, yPos), behavior: 'smooth' });

        timerAlerta = setTimeout(() => {
            cerrarAlertaAnimada();
        }, 7000);
    }

    function cerrarAlertaAnimada() {
        if(timerAlerta) {
            clearTimeout(timerAlerta);
            timerAlerta = null;
        }
        const alerta = document.getElementById('alerta_activa');
        if(!alerta) {
            const contenedor = document.getElementById('alerta_cancelacion');
            if(contenedor) contenedor.innerHTML = '';
            return;
        }
        alerta.classList.add('alerta-reserva--saliendo');
        setTimeout(() => {
            const contenedor = document.getElementById('alerta_cancelacion');
            if(contenedor) contenedor.innerHTML = '';
        }, 240);
    }
}

/**
 * ==============================================================================
 * 4. CRONOGRAMA SEMANAL / GRILLA DE HORARIOS
 * Vista: views/templates/grilla-horarios.php
 * ==============================================================================
 */
function iniciarGrillaHorarios() {
    const tabs = document.querySelectorAll('.cronograma-tabs-dias .tab-dia-btn');
    const paneles = document.querySelectorAll('.cronograma-paneles .panel-dia');
    const filtros = document.querySelectorAll('.cronograma-filtros .filtro-btn');
    const tarjetas = document.querySelectorAll('.tarjeta-horario');

    if(tabs.length === 0 && filtros.length === 0) return;

    // Manejo de tabs de días
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('activo'));
            this.classList.add('activo');

            const diaId = this.getAttribute('data-dia');
            paneles.forEach(panel => {
                if(panel.getAttribute('data-panel-dia') === diaId) {
                    panel.classList.add('panel-dia--activo');
                } else {
                    panel.classList.remove('panel-dia--activo');
                }
            });
        });
    });

    // Filtro por disciplina/plan
    filtros.forEach(btn => {
        btn.addEventListener('click', function() {
            filtros.forEach(b => b.classList.remove('activo'));
            this.classList.add('activo');

            const planSeleccionado = this.getAttribute('data-plan');

            tarjetas.forEach(tarjeta => {
                if(planSeleccionado === 'todos' || tarjeta.getAttribute('data-plan-id') === planSeleccionado) {
                    tarjeta.style.display = 'flex';
                } else {
                    tarjeta.style.display = 'none';
                }
            });
        });
    });
}

/**
 * ==============================================================================
 * 5. COPIAR EMAIL DE CONTACTO AL PORTAPAPELES
 * Vista: views/paginas/index.php
 * ==============================================================================
 */
function iniciarCopiarEmail() {
    window.copiarEmail = function(email, btn) {
        const feedbackCopiado = () => {
            const span = btn.querySelector('span');
            const icono = btn.querySelector('i');
            const textoOriginal = span ? span.innerText : '';
            btn.classList.add('btn-copiar-email--copiado');
            if (span) span.innerText = '¡Copiado!';
            if (icono) icono.className = 'fa-solid fa-check';
            setTimeout(() => {
                btn.classList.remove('btn-copiar-email--copiado');
                if (span) span.innerText = textoOriginal;
                if (icono) icono.className = 'fa-regular fa-copy';
            }, 2000);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(email)
                .then(feedbackCopiado)
                .catch(() => fallbackCopiar(email, feedbackCopiado));
        } else {
            fallbackCopiar(email, feedbackCopiado);
        }
    };

    function fallbackCopiar(texto, callback) {
        const inputTemp = document.createElement('input');
        inputTemp.value = texto;
        document.body.appendChild(inputTemp);
        inputTemp.select();
        try {
            document.execCommand('copy');
            if (callback) callback();
        } catch (err) {
            console.error('Error al copiar:', err);
        }
        if (inputTemp.parentNode) {
            inputTemp.parentNode.removeChild(inputTemp);
        }
    }
}

/**
 * ==============================================================================
 * 6. GESTIÓN Y CRONOGRAMA DE ENTRENADORES (ADMIN)
 * Vista: views/admin/entrenadores/index.php
 * ==============================================================================
 */
function iniciarAdminEntrenadores() {
    const modalClases = document.getElementById('modal_clases');
    if (!modalClases && !document.querySelector('.fila-entrenador')) return;

    window.filtrarEntrenadores = function(termino) {
        const q = (termino || '').trim().toLowerCase();
        const filas = document.querySelectorAll('.fila-entrenador');

        filas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre') || '';
            const email = fila.getAttribute('data-email') || '';
            const telefono = fila.getAttribute('data-telefono') || '';

            if (q === '' || nombre.includes(q) || email.includes(q) || telefono.includes(q)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    };

    window.verClasesEntrenador = async function(id, nombre) {
        const modal = document.getElementById('modal_clases');
        const modalNombre = document.getElementById('modal_entrenador_nombre');
        const modalBody = document.getElementById('modal_clases_body');

        if (modalNombre) modalNombre.textContent = nombre;
        if (modal) modal.classList.add('activo');
        document.body.style.overflow = 'hidden';

        if (modalBody) {
            modalBody.innerHTML = `
                <div class="modal-loading">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    <p>Cargando cronograma semanal...</p>
                </div>
            `;
        }

        try {
            const respuesta = await fetch(`/api/entrenador/horarios?id=${id}`);
            if (!respuesta.ok) throw new Error('Error en la petición');
            const clases = await respuesta.json();

            if (!clases || clases.length === 0) {
                if (modalBody) {
                    modalBody.innerHTML = `
                        <div class="modal-vacio">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            <p>No se encontraron clases asignadas para este entrenador.</p>
                        </div>
                    `;
                }
                return;
            }

            let html = '<div class="lista-clases-modal">';
            clases.forEach(c => {
                html += `
                    <div class="item-clase-modal">
                        <div class="item-clase-modal__dia">
                            ${c.dia_nombre}
                        </div>
                        <div class="item-clase-modal__datos">
                            <strong>${c.plan_nombre}</strong>
                            <span>
                                <i class="fa-regular fa-clock"></i> ${c.hora_inicio} a ${c.hora_fin} hs
                            </span>
                            ${c.descripcion ? `<small style="display: block; color: #64748b; font-size: 1.2rem; margin-top: 0.2rem;"><i class="fa-solid fa-circle-info" style="color: #0ea5e9;"></i> ${c.descripcion}</small>` : ''}
                        </div>
                        <div class="item-clase-modal__cupo" title="Capacidad máxima de la clase">
                            <i class="fa-solid fa-users"></i> ${c.cupo ? `${c.cupo} cupos` : 'Cupo ilimitado'}
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            if (modalBody) modalBody.innerHTML = html;
        } catch (error) {
            if (modalBody) {
                modalBody.innerHTML = `
                    <div class="modal-vacio">
                        <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i>
                        <p>Ocurrió un error al cargar los horarios. Por favor intentá nuevamente.</p>
                    </div>
                `;
            }
        }
    };

    window.cerrarModalClases = function() {
        const modal = document.getElementById('modal_clases');
        if (modal) {
            modal.classList.remove('activo');
            document.body.style.overflow = '';
        }
    };

    window.cerrarModalAfuera = function(e) {
        if (e && e.target && e.target.id === 'modal_clases') {
            window.cerrarModalClases();
        }
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.cerrarModalClases();
        }
    });
}

/**
 * ==============================================================================
 * 7. CATÁLOGO Y FILTROS DE EJERCICIOS (ADMIN)
 * Vista: views/admin/ejercicios/index.php
 * ==============================================================================
 */
function iniciarAdminEjercicios() {
    if (!document.getElementById('filtro_ejercicios') && !document.querySelector('.card-ejercicio')) return;

    let grupoActivo = '';

    window.seleccionarGrupo = function(boton, grupo) {
        document.querySelectorAll('.filtro-chip').forEach(btn => btn.classList.remove('activo'));
        if (boton) boton.classList.add('activo');
        grupoActivo = grupo;
        window.filtrarCatalogo();
    };

    window.filtrarCatalogo = function() {
        const input = document.getElementById('filtro_ejercicios');
        const busqueda = (input ? input.value : '').trim().toLowerCase();
        const tarjetas = document.querySelectorAll('.card-ejercicio');
        const sinResultados = document.getElementById('sin_resultados');
        const contador = document.getElementById('contador_visibles');
        let visibles = 0;

        tarjetas.forEach(card => {
            const nombre = card.getAttribute('data-nombre') || '';
            const musculo = card.getAttribute('data-musculo') || '';
            const descripcion = card.getAttribute('data-descripcion') || '';

            const coincideGrupo = (grupoActivo === '' || musculo === grupoActivo);
            const coincideBusqueda = (busqueda === '' || nombre.includes(busqueda) || descripcion.includes(busqueda));

            if (coincideGrupo && coincideBusqueda) {
                card.style.display = '';
                visibles++;
            } else {
                card.style.display = 'none';
            }
        });

        if (contador) contador.textContent = visibles;
        if (sinResultados) {
            sinResultados.style.display = (visibles === 0 && tarjetas.length > 0) ? 'block' : 'none';
        }
    };
}

/**
 * ==============================================================================
 * 8. GESTIÓN Y FILTROS DE CLIENTES/USUARIOS (ADMIN)
 * Vista: views/admin/usuarios/index.php
 * ==============================================================================
 */
function iniciarAdminUsuarios() {
    if (!document.getElementById('tabla_clientes') && !document.getElementById('filtro_busqueda_clientes')) return;

    let estadoChipActivo = 'todos';

    window.seleccionarFiltroEstado = function(boton, filtro) {
        document.querySelectorAll('.ejercicios-toolbar__chips .filtro-chip').forEach(btn => {
            btn.classList.remove('activo');
        });
        if (boton) boton.classList.add('activo');
        estadoChipActivo = filtro;
        window.filtrarTablaClientes();
    };

    window.filtrarTablaClientes = function() {
        const texto = (document.getElementById('filtro_busqueda_clientes')?.value || '').toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-cliente');
        let visibles = 0;

        filas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre') || '';
            const dni = fila.getAttribute('data-dni') || '';
            const email = fila.getAttribute('data-email') || '';
            const telefono = fila.getAttribute('data-telefono') || '';
            const estado = fila.getAttribute('data-confirmado') || '';
            const membresia = fila.getAttribute('data-membresia') || '';

            const coincideTexto = !texto || nombre.includes(texto) || dni.includes(texto) || email.includes(texto) || telefono.includes(texto);

            let coincideFiltro = true;
            if (estadoChipActivo === 'confirmados') {
                coincideFiltro = (estado === 'confirmados');
            } else if (estadoChipActivo === 'pendientes') {
                coincideFiltro = (estado === 'pendientes');
            } else if (estadoChipActivo === 'con_membresia') {
                coincideFiltro = (membresia === 'con_membresia');
            }

            if (coincideTexto && coincideFiltro) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        const contador = document.getElementById('contador_visibles');
        if (contador) contador.textContent = visibles;

        const sinResultados = document.getElementById('sin_resultados_clientes');
        const tabla = document.getElementById('tabla_clientes');
        if (sinResultados && tabla) {
            if (visibles === 0) {
                sinResultados.style.display = 'block';
                tabla.closest('.tabla-contenedor').style.display = 'none';
            } else {
                sinResultados.style.display = 'none';
                tabla.closest('.tabla-contenedor').style.display = '';
            }
        }
    };
}

/**
 * ==============================================================================
 * 9. PREVISUALIZACIÓN DE IMÁGENES EN CONFIGURACIÓN (ADMIN)
 * Vista: views/admin/configuracion/index.php
 * ==============================================================================
 */
function iniciarAdminConfiguracion() {
    window.previsualizarImagen = function(input, idImg, idPlaceholder) {
        if (input && input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(idImg);
                const placeholder = document.getElementById(idPlaceholder);
                if (img) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    };
}

/**
 * ==============================================================================
 * 10. GENERACIÓN DE BLOQUES EN FORMULARIO DE HORARIOS (ADMIN)
 * Vista: views/admin/horarios/formulario.php
 * ==============================================================================
 */
function iniciarAdminHorariosFormulario() {
    const checkBloques = document.getElementById('generar_bloques');
    const panelBloques = document.getElementById('opciones_bloques');
    if (!checkBloques || !panelBloques) return;

    checkBloques.addEventListener('change', function() {
        panelBloques.style.display = this.checked ? 'block' : 'none';
    });
}

/**
 * ==============================================================================
 * 11. AGENDA DE RESERVAS Y CONTROL DE ASISTENCIA (ADMIN)
 * Vista: views/admin/reservas/index.php
 * ==============================================================================
 */
function iniciarAdminReservas() {
    const esAdminReservas = document.querySelector('.clase-agenda-card') && !document.getElementById('total_presentes_kpi');
    if (!esAdminReservas) return;

    // Toggle para desplegar / ocultar lista de alumnos
    document.querySelectorAll('.btn-toggle-alumnos').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            if (!targetEl) return;

            const textoSpan = this.querySelector('.btn-toggle-texto');
            const flecha = this.querySelector('.flecha-toggle');
            const estaOculto = targetEl.style.display === 'none' || targetEl.style.display === '';

            if (estaOculto) {
                targetEl.style.display = 'block';
                this.classList.add('activo');
                if (textoSpan) textoSpan.textContent = 'Ocultar Alumnos';
                if (flecha) {
                    flecha.classList.remove('fa-chevron-down');
                    flecha.classList.add('fa-chevron-up');
                }
            } else {
                targetEl.style.display = 'none';
                this.classList.remove('activo');
                if (textoSpan) textoSpan.textContent = 'Ver Alumnos';
                if (flecha) {
                    flecha.classList.remove('fa-chevron-up');
                    flecha.classList.add('fa-chevron-down');
                }
            }
        });
    });

    // Filtro en tiempo real por alumno o clase
    window.filtrarAgenda = function(termino) {
        const q = (termino || '').trim().toLowerCase();
        const cards = document.querySelectorAll('.clase-agenda-card');

        cards.forEach(card => {
            const clase = card.getAttribute('data-clase') || '';
            const profesor = card.getAttribute('data-profesor') || '';
            const filas = card.querySelectorAll('.fila-alumno');
            
            let coincidenciaEnAlumnos = false;

            filas.forEach(fila => {
                const alumno = fila.getAttribute('data-alumno') || '';
                const email = fila.getAttribute('data-email') || '';

                if (q === '' || alumno.includes(q) || email.includes(q) || clase.includes(q) || profesor.includes(q)) {
                    fila.style.display = '';
                    coincidenciaEnAlumnos = true;
                } else {
                    fila.style.display = 'none';
                }
            });

            if (q === '' || clase.includes(q) || profesor.includes(q) || coincidenciaEnAlumnos) {
                card.style.display = '';
                
                if (coincidenciaEnAlumnos && q !== '') {
                    const bodyEl = card.querySelector('.clase-agenda-body');
                    const toggleBtn = card.querySelector('.btn-toggle-alumnos');
                    if (bodyEl) bodyEl.style.display = 'block';
                    if (toggleBtn) {
                        toggleBtn.classList.add('activo');
                        const span = toggleBtn.querySelector('.btn-toggle-texto');
                        const flecha = toggleBtn.querySelector('.flecha-toggle');
                        if (span) span.textContent = 'Ocultar Alumnos';
                        if (flecha) {
                            flecha.classList.remove('fa-chevron-down');
                            flecha.classList.add('fa-chevron-up');
                        }
                    }
                }
            } else {
                card.style.display = 'none';
            }
        });
    };

    // Cancelar reserva de alumno desde el panel de admin
    document.querySelectorAll('.btn-cancelar-admin').forEach(btn => {
        btn.addEventListener('click', async function() {
            const reservaId = this.getAttribute('data-id');
            const cliente = this.getAttribute('data-nombre');
            const clase = this.getAttribute('data-clase');

            if (!confirm(`¿Deseas dar de baja la reserva de ${cliente} para la clase de ${clase}? Se liberará el cupo.`)) {
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                const res = await fetch('/api/reservas/cancelar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reserva_id: reservaId })
                });
                const data = await res.json();
                if (data.tipo === 'exito') {
                    window.location.reload();
                } else {
                    alert(data.mensaje || 'Error al cancelar la reserva');
                    this.disabled = false;
                    this.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                }
            } catch (e) {
                console.error(e);
                alert('Error de conexión al procesar la cancelación');
                this.disabled = false;
                this.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
            }
        });
    });

    // Marcar / Desmarcar Asistencia vía API
    document.querySelectorAll('.btn-asistencia-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            const contenedor = this.closest('.control-asistencia');
            if (!contenedor) return;
            const reservaId = contenedor.getAttribute('data-reserva-id');
            const valor = parseInt(this.getAttribute('data-valor'), 10);
            const hermanos = contenedor.querySelectorAll('.btn-asistencia-item');

            if (this.classList.contains('activo')) {
                return;
            }

            hermanos.forEach(b => b.disabled = true);
            const iconoOriginal = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                const res = await fetch('/api/asistencias/marcar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reserva_id: reservaId, presente: valor })
                });

                const data = await res.json();

                if (data.tipo === 'exito') {
                    hermanos.forEach(b => b.classList.remove('activo'));
                    this.classList.add('activo');

                    const cardClase = contenedor.closest('.clase-agenda-card');
                    if (cardClase) {
                        const totalPresentes = cardClase.querySelectorAll('.btn-asistencia-item--presente.activo').length;
                        const badgePresentes = cardClase.querySelector('.contador-presentes-clase');
                        if (badgePresentes) {
                            badgePresentes.innerHTML = `<i class="fa-solid fa-user-check"></i> ${totalPresentes} presentes`;
                        }
                    }
                } else {
                    alert(data.mensaje || 'Error al registrar la asistencia');
                }
            } catch (e) {
                console.error(e);
                alert('Error de conexión al registrar asistencia');
            } finally {
                this.innerHTML = iconoOriginal;
                hermanos.forEach(b => b.disabled = false);
            }
        });
    });
}

/**
 * ==============================================================================
 * 12. CONTROL DE ASISTENCIA DIARIA DEL ENTRENADOR
 * Vista: views/entrenador/asistencia.php
 * ==============================================================================
 */
function iniciarEntrenadorAsistencia() {
    const kpiPresentes = document.getElementById('total_presentes_kpi');
    if (!kpiPresentes) return;

    // Toggle de apertura / cierre de alumnos por clase
    document.querySelectorAll('.btn-toggle-alumnos').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const target = document.getElementById(targetId);
            const flecha = this.querySelector('.flecha-toggle');
            const spanTexto = this.querySelector('.btn-toggle-texto');

            if (!target) return;

            if (target.style.display === 'none') {
                target.style.display = 'block';
                this.classList.add('activo');
                if (spanTexto) spanTexto.textContent = 'Ocultar Alumnos';
                if (flecha) {
                    flecha.classList.remove('fa-chevron-down');
                    flecha.classList.add('fa-chevron-up');
                }
            } else {
                target.style.display = 'none';
                this.classList.remove('activo');
                if (spanTexto) spanTexto.textContent = 'Ver Alumnos';
                if (flecha) {
                    flecha.classList.remove('fa-chevron-up');
                    flecha.classList.add('fa-chevron-down');
                }
            }
        });
    });

    // Marcar / Desmarcar Asistencia vía API interactiva
    document.querySelectorAll('.btn-asistencia-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            const contenedor = this.closest('.control-asistencia');
            if (!contenedor) return;
            const reservaId = contenedor.getAttribute('data-reserva-id');
            const valor = parseInt(this.getAttribute('data-valor'), 10);
            const hermanos = contenedor.querySelectorAll('.btn-asistencia-item');

            if (this.classList.contains('activo')) {
                return;
            }

            hermanos.forEach(b => b.disabled = true);
            const iconoOriginal = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                const res = await fetch('/api/asistencias/marcar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reserva_id: reservaId, presente: valor })
                });

                const data = await res.json();

                if (data.tipo === 'exito') {
                    hermanos.forEach(b => b.classList.remove('activo'));
                    this.classList.add('activo');

                    // Actualizar contadores de la clase y KPI global
                    const cardClase = contenedor.closest('.clase-agenda-card');
                    if (cardClase) {
                        const totalPresentesClase = cardClase.querySelectorAll('.btn-asistencia-item--presente.activo').length;
                        const badgePresentes = cardClase.querySelector('.contador-presentes-clase');
                        if (badgePresentes) {
                            badgePresentes.innerHTML = `<i class="fa-solid fa-user-check"></i> ${totalPresentesClase} presentes`;
                        }
                    }

                    const totalPresentesGlobal = document.querySelectorAll('.btn-asistencia-item--presente.activo').length;
                    kpiPresentes.textContent = totalPresentesGlobal;
                } else {
                    alert(data.mensaje || 'Error al registrar la asistencia');
                }
            } catch (e) {
                console.error(e);
                alert('Error de conexión al registrar la asistencia');
            } finally {
                this.innerHTML = iconoOriginal;
                hermanos.forEach(b => b.disabled = false);
            }
        });
    });
}

/**
 * ==============================================================================
 * 13. LISTA Y FILTROS DE ALUMNOS DEL ENTRENADOR
 * Vista: views/entrenador/alumnos/index.php
 * ==============================================================================
 */
function iniciarEntrenadorAlumnos() {
    if (!document.getElementById('tabla_alumnos_entrenador') && !document.getElementById('filtro_busqueda_alumnos')) return;

    let filtroChipAlumno = 'todos';

    window.seleccionarFiltroAlumnos = function(boton, filtro) {
        document.querySelectorAll('.ejercicios-toolbar__chips .filtro-chip').forEach(btn => {
            btn.classList.remove('activo');
        });
        if (boton) boton.classList.add('activo');
        filtroChipAlumno = filtro;
        window.filtrarTablaAlumnos();
    };

    window.filtrarTablaAlumnos = function() {
        const texto = (document.getElementById('filtro_busqueda_alumnos')?.value || '').toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-alumno-entrenador');
        let visibles = 0;

        filas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre') || '';
            const dni = fila.getAttribute('data-dni') || '';
            const email = fila.getAttribute('data-email') || '';
            const telefono = fila.getAttribute('data-telefono') || '';
            const enMisClases = fila.getAttribute('data-en-mis-clases') === '1';
            const conRutina = fila.getAttribute('data-con-rutina') === '1';

            const coincideTexto = !texto || nombre.includes(texto) || dni.includes(texto) || email.includes(texto) || telefono.includes(texto);

            let coincideFiltro = true;
            if (filtroChipAlumno === 'en_mis_clases') {
                coincideFiltro = enMisClases;
            } else if (filtroChipAlumno === 'con_rutina') {
                coincideFiltro = conRutina;
            }

            if (coincideTexto && coincideFiltro) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        const contador = document.getElementById('contador_alumnos_visibles');
        if (contador) contador.textContent = visibles;

        const sinResultados = document.getElementById('sin_resultados_alumnos_entrenador');
        const tabla = document.getElementById('tabla_alumnos_entrenador');
        if (sinResultados && tabla) {
            if (visibles === 0) {
                sinResultados.style.display = 'block';
                tabla.closest('.tabla-contenedor').style.display = 'none';
            } else {
                sinResultados.style.display = 'none';
                tabla.closest('.tabla-contenedor').style.display = '';
            }
        }
    };
}

/**
 * ==============================================================================
 * 14. PESTAÑAS DE GÉNERO EN DETALLE DE RUTINA / PIZARRA WOD
 * Vista: views/admin/rutinas/detalle.php
 * ==============================================================================
 */
function iniciarDetalleRutinaWod() {
    window.cambiarPestanaDetalle = function(btn, idDia, genero) {
        if (!btn) return;
        const parentTabs = btn.parentElement;
        if (parentTabs) {
            parentTabs.querySelectorAll('.btn-pizarra-genero').forEach(b => b.classList.remove('active'));
        }
        btn.classList.add('active');

        const seccion = btn.closest('.pizarra-wod__seccion');
        if (!seccion) return;
        const panelH = seccion.querySelector('.panel-genero-pizarra--hombres');
        const panelM = seccion.querySelector('.panel-genero-pizarra--mujeres');

        if (genero === 'hombres') {
            if (panelH) panelH.style.display = 'block';
            if (panelM) panelM.style.display = 'none';
        } else {
            if (panelH) panelH.style.display = 'none';
            if (panelM) panelM.style.display = 'block';
        }
    };
}

/**
 * ==============================================================================
 * 15. MOSTRAR / OCULTAR CONTRASEÑA (PASSWORD TOGGLE)
 * Vistas: Todas las que contengan inputs de tipo password
 * (Login, Registro, Recuperar, Perfil/Cuenta, Admin Usuarios, Entrenadores)
 * ==============================================================================
 */
function iniciarPasswordToggle() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    if (!passwordInputs.length) return;

    passwordInputs.forEach(input => {
        // Evitar duplicar si ya fue inicializado
        if (input.dataset.hasPasswordToggle === 'true' || (input.parentElement && input.parentElement.classList.contains('password-toggle-wrapper'))) {
            return;
        }

        input.dataset.hasPasswordToggle = 'true';

        // Crear contenedor relativo
        const wrapper = document.createElement('div');
        wrapper.className = 'password-toggle-wrapper';

        // Insertar contenedor y mover el input adentro
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        // Crear botón toggle
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'btn-password-toggle';
        toggleBtn.setAttribute('aria-label', 'Mostrar contraseña');
        toggleBtn.setAttribute('title', 'Mostrar contraseña');
        toggleBtn.tabIndex = -1; // Evita interrumpir navegación secuencial con tabulador
        toggleBtn.innerHTML = '<i class="fa-solid fa-eye"></i>';

        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const esPassword = input.type === 'password';
            input.type = esPassword ? 'text' : 'password';

            const icon = toggleBtn.querySelector('i');
            if (icon) {
                if (esPassword) {
                    icon.className = 'fa-solid fa-eye-slash';
                    toggleBtn.setAttribute('aria-label', 'Ocultar contraseña');
                    toggleBtn.setAttribute('title', 'Ocultar contraseña');
                    toggleBtn.classList.add('active');
                } else {
                    icon.className = 'fa-solid fa-eye';
                    toggleBtn.setAttribute('aria-label', 'Mostrar contraseña');
                    toggleBtn.setAttribute('title', 'Mostrar contraseña');
                    toggleBtn.classList.remove('active');
                }
            }

            input.focus();
        });

        wrapper.appendChild(toggleBtn);

        // Si el formulario se resetea, restaurar el tipo a password
        if (input.form) {
            input.form.addEventListener('reset', () => {
                setTimeout(() => {
                    input.type = 'password';
                    const icon = toggleBtn.querySelector('i');
                    if (icon) icon.className = 'fa-solid fa-eye';
                    toggleBtn.setAttribute('aria-label', 'Mostrar contraseña');
                    toggleBtn.setAttribute('title', 'Mostrar contraseña');
                    toggleBtn.classList.remove('active');
                }, 0);
            });
        }
    });
}

