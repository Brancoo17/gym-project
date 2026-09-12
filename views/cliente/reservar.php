<?php
/** @var string $nombre */
/** @var array $planes */
$planes = $planes ?? [];
$hoy = date('Y-m-d');
$nombresDias = [
    1 => 'Lun',
    2 => 'Mar',
    3 => 'Mié',
    4 => 'Jue',
    5 => 'Vie',
    6 => 'Sáb'
];
$nombresMeses = [
    1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
    7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
];

// Generar 14 días hábiles consecutivos (Lunes a Sábado, omitiendo domingos)
$diasDisponibles = [];
$cursor = new DateTime();
while(count($diasDisponibles) < 14) {
    $w = (int)$cursor->format('w'); // 0 = Domingo
    if($w !== 0) {
        $fechaYmd = $cursor->format('Y-m-d');
        $diasDisponibles[] = [
            'fecha' => $fechaYmd,
            'dia_semana' => $nombresDias[$w] ?? '',
            'dia_numero' => $cursor->format('d'),
            'mes' => $nombresMeses[(int)$cursor->format('n')] ?? '',
            'es_hoy' => ($fechaYmd === $hoy)
        ];
    }
    $cursor->modify('+1 day');
}

$fechaInicial = $diasDisponibles[0]['fecha'] ?? $hoy;
?>

<div class="reservar-header">
    <div>
        <h1>Reservar Turno</h1>
        <p>Seleccioná una fecha y asegurá tu lugar en las clases disponibles.</p>
    </div>
    <a href="/cliente/turnos" class="boton-accion">
        <i class="fa-solid fa-clipboard-list"></i> Ver Mis Turnos
    </a>
</div>

<?php if(empty($planes)): ?>
    <div class="banner-alerta-sin-membresia" style="margin: 0 0 2.5rem 0;">
        <div class="banner-alerta-sin-membresia__icono">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="banner-alerta-sin-membresia__cuerpo">
            <div class="banner-alerta-sin-membresia__header">
                <h3>No tenés membresías activas para reservar turnos</h3>
                <span class="badge badge--rojo"><i class="fa-solid fa-lock"></i> Requerido</span>
            </div>
            <p>Para poder ver las clases disponibles y asegurar tu lugar en el cronograma semanal, necesitás contratar o renovar un plan de entrenamiento.</p>
            <div class="banner-alerta-sin-membresia__acciones">
                <a href="/cliente/planes" class="boton boton--primario">
                    <i class="fa-solid fa-cart-shopping"></i> Ver Planes y Membresías
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Selector de Días Semanales Tipo Tabs (Mobile-First / Carrusel) -->
<div class="selector-dias-semana">
    <div class="selector-dias-semana__header">
        <div class="selector-dias-semana__titulo">
            <i class="fa-regular fa-calendar-days"></i>
            <span>Días Disponibles</span>
        </div>

        <div class="selector-dias-semana__acciones">
            <!-- Flechas de navegación (visible en tablet/desktop) -->
            <div class="nav-dias-flechas">
                <button type="button" class="btn-flecha-dia" id="btn_dia_anterior" title="Desplazar a la izquierda" aria-label="Anterior">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button type="button" class="btn-flecha-dia" id="btn_dia_siguiente" title="Desplazar a la derecha" aria-label="Siguiente">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <!-- Selector de fecha libre / manual -->
            <div class="btn-fecha-especifica" id="btn_otra_fecha" title="Seleccionar otra fecha en el calendario" role="button" tabindex="0">
                <i class="fa-regular fa-calendar"></i>
                <span id="label_fecha_seleccionada">Otra fecha</span>
                <input type="date" 
                       id="selector_fecha_manual" 
                       class="input-fecha-oculto" 
                       value="<?php echo $fechaInicial; ?>" 
                       min="<?php echo $hoy; ?>"
                       aria-label="Seleccionar fecha en el calendario">
            </div>
        </div>
    </div>

    <!-- Carrusel horizontal de pestañas de días -->
    <div class="carrusel-dias" id="carrusel_dias" role="tablist" data-fecha-minima="<?php echo $hoy; ?>" data-fecha-inicial="<?php echo $fechaInicial; ?>">
        <?php foreach($diasDisponibles as $dia): ?>
            <button type="button" 
                    class="tab-dia <?php echo ($dia['fecha'] === $fechaInicial) ? 'activo' : ''; ?> <?php echo $dia['es_hoy'] ? 'tab-dia--hoy' : ''; ?>"
                    data-fecha="<?php echo $dia['fecha']; ?>"
                    role="tab"
                    aria-selected="<?php echo ($dia['fecha'] === $fechaInicial) ? 'true' : 'false'; ?>">
                <?php if($dia['es_hoy']): ?>
                    <span class="tab-dia__badge-hoy">HOY</span>
                <?php endif; ?>
                <span class="tab-dia__semana"><?php echo $dia['dia_semana']; ?></span>
                <span class="tab-dia__numero"><?php echo $dia['dia_numero']; ?></span>
                <span class="tab-dia__mes"><?php echo $dia['mes']; ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <div id="aviso_domingo" class="aviso-domingo" style="display: none;">
        <i class="fa-solid fa-triangle-exclamation"></i> El gimnasio permanece cerrado los días domingo. Por favor elegí una fecha de lunes a sábado.
    </div>
</div>

<!-- Barra de Filtros Dinámicos (Disciplina y Franja Horaria) -->
<div class="filtros-reservas" id="filtros_reservas" style="display: none;">
    <div class="filtros-reservas__grupo">
        <span class="filtros-reservas__label">
            <i class="fa-solid fa-layer-group"></i> Disciplina:
        </span>
        <div class="filtros-reservas__pills" id="filtro_disciplinas">
            <button type="button" class="filtro-pill activo" data-disciplina="todas">
                Todas
            </button>
            <?php foreach($planes as $plan): ?>
                <button type="button" class="filtro-pill" data-disciplina="<?php echo s(strtolower(trim($plan->nombre))); ?>">
                    <?php echo s($plan->nombre); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="filtros-reservas__grupo">
        <span class="filtros-reservas__label">
            <i class="fa-regular fa-clock"></i> Horario:
        </span>
        <div class="filtros-reservas__pills" id="filtro_turnos">
            <button type="button" class="filtro-pill activo" data-turno="todos">
                Todo el día
            </button>
            <button type="button" class="filtro-pill" data-turno="manana">
                <i class="fa-regular fa-sun"></i> Mañana <span class="filtro-pill__sub">(< 12 hs)</span>
            </button>
            <button type="button" class="filtro-pill" data-turno="tarde">
                <i class="fa-solid fa-cloud-sun"></i> Tarde <span class="filtro-pill__sub">(12 a 18 hs)</span>
            </button>
            <button type="button" class="filtro-pill" data-turno="noche">
                <i class="fa-regular fa-moon"></i> Noche <span class="filtro-pill__sub">(> 18 hs)</span>
            </button>
        </div>
    </div>

    <div class="filtros-reservas__footer">
        <div class="filtros-reservas__conteo" id="contador_resultados">
            <span>Consultando turnos...</span>
        </div>
        <button type="button" class="btn-limpiar-filtros" id="btn_limpiar_filtros" style="display: none;">
            <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
        </button>
    </div>
</div>

<!-- Alertas dinámicas -->
<div id="contenedor_alertas"></div>

<!-- Contenedor dinámico de turnos -->
<div id="contenedor_turnos">
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
</div>

<!-- Modal de Confirmación de Cancelación de Turno (Paso 4) -->
<div class="modal-overlay" id="modal_confirmar_cancelar" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-confirmar-cancelar">
        <div class="modal-confirmar-cancelar__icono">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-confirmar-cancelar__titulo">¿Cancelar este turno?</h3>
        <p class="modal-confirmar-cancelar__mensaje">
            Se liberará tu lugar en la clase para que otro alumno pueda reservarlo.
        </p>

        <div class="modal-confirmar-cancelar__info-clase">
            <div><i class="fa-solid fa-dumbbell"></i> <strong id="modal_cancelar_clase">-</strong></div>
            <div><i class="fa-regular fa-calendar"></i> <span id="modal_cancelar_fecha">-</span></div>
            <div><i class="fa-regular fa-clock"></i> <span id="modal_cancelar_hora">-</span></div>
        </div>

        <div class="modal-confirmar-cancelar__acciones">
            <button type="button" class="btn-mantener" id="btn_modal_cancelar_cerrar">
                Mantener mi turno
            </button>
            <button type="button" class="btn-confirmar-eliminar" id="btn_modal_cancelar_confirmar">
                <i class="fa-solid fa-trash-can"></i> Sí, cancelar
            </button>
        </div>
    </div>
</div>
