<?php
/** @var string $nombre */
/** @var string $periodo */
/** @var string $tituloPeriodo */
/** @var array $kpis */
/** @var array $pagos */
/** @var array $porPlan */
/** @var array $proximosVencimientos */

$periodo = $periodo ?? 'mes_actual';
$tituloPeriodo = $tituloPeriodo ?? 'Mes Actual';
$kpis = $kpis ?? [
    'totalTransacciones' => 0,
    'facturacionTotal' => 0,
    'cantAprobados' => 0,
    'cantPendientes' => 0,
    'cantRechazados' => 0,
    'ticketPromedio' => 0,
    'montoMercadoPago' => 0,
    'cantMercadoPago' => 0,
    'montoEfectivo' => 0,
    'cantEfectivo' => 0
];
$pagos = $pagos ?? [];
$porPlan = $porPlan ?? [];
$proximosVencimientos = $proximosVencimientos ?? [];

$totalFacturado = (float)$kpis['facturacionTotal'];
$montoMP = (float)$kpis['montoMercadoPago'];
$montoEf = (float)$kpis['montoEfectivo'];
$pctMP = ($totalFacturado > 0) ? round(($montoMP / $totalFacturado) * 100) : 0;
$pctEf = ($totalFacturado > 0) ? round(($montoEf / $totalFacturado) * 100) : 0;
?>

<div class="reportes-header">
    <div class="reportes-header__info">
        <div class="reportes-header__titulo">
            <h1>Pagos y Facturación</h1>
            <span class="badge-periodo">
                <i class="fa-solid fa-calendar-check"></i> <?php echo s($tituloPeriodo); ?>
            </span>
        </div>
        <p>Control de ingresos, cobros en mostrador, transacciones de Mercado Pago y proyección de vencimientos.</p>
    </div>

    <div class="reportes-header__acciones no-print">
        <button type="button" onclick="window.print()" class="boton-accion">
            <i class="fa-solid fa-print"></i> Imprimir Informe
        </button>
        <a href="/admin/usuarios" class="boton">
            <i class="fa-solid fa-address-book"></i> Gestión de Clientes
        </a>
    </div>
</div>

<!-- Selector de Período (Pestañas Rápidas) -->
<div class="reportes-filtros no-print">
    <a href="/admin/pagos?periodo=mes_actual" 
       class="btn-filtro-periodo <?php echo $periodo === 'mes_actual' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-regular fa-calendar"></i> Mes Actual
    </a>
    <a href="/admin/pagos?periodo=mes_anterior" 
       class="btn-filtro-periodo <?php echo $periodo === 'mes_anterior' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-solid fa-clock-rotate-left"></i> Mes Anterior
    </a>
    <a href="/admin/pagos?periodo=ultimos_30" 
       class="btn-filtro-periodo <?php echo $periodo === 'ultimos_30' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-solid fa-hourglass-half"></i> Últimos 30 Días
    </a>
    <a href="/admin/pagos?periodo=anio_actual" 
       class="btn-filtro-periodo <?php echo $periodo === 'anio_actual' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-solid fa-calendar-days"></i> Todo el <?php echo date('Y'); ?>
    </a>
    <a href="/admin/pagos?periodo=historico" 
       class="btn-filtro-periodo <?php echo $periodo === 'historico' ? 'btn-filtro-periodo--activo' : ''; ?>">
        <i class="fa-solid fa-database"></i> Histórico Completo
    </a>
</div>

<!-- KPIs Principales de Facturación -->
<div class="agenda-metricas-grid">
    <div class="metrica-card metrica-card--borde-verde">
        <div class="metrica-card__icono metrica-card__icono--verde">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
        <div>
            <span class="metrica-card__label">Facturación Aprobada</span>
            <strong class="metrica-card__valor metrica-card__valor--verde">
                $<?php echo number_format($kpis['facturacionTotal'], 2, ',', '.'); ?>
            </strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-azul">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Pagos Aprobados</span>
            <strong class="metrica-card__valor">
                <?php echo $kpis['cantAprobados']; ?> 
                <span class="metrica-card__subvalor">(ticket prom: $<?php echo number_format($kpis['ticketPromedio'], 2, ',', '.'); ?>)</span>
            </strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-amarillo">
        <div class="metrica-card__icono metrica-card__icono--naranja">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <span class="metrica-card__label">Pagos Pendientes</span>
            <strong class="metrica-card__valor metrica-card__valor--amarillo">
                <?php echo $kpis['cantPendientes']; ?>
            </strong>
        </div>
    </div>

    <div class="metrica-card metrica-card--borde-rojo">
        <div class="metrica-card__icono metrica-card__icono--rojo">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div>
            <span class="metrica-card__label">Pagos Rechazados</span>
            <strong class="metrica-card__valor metrica-card__valor--rojo">
                <?php echo $kpis['cantRechazados']; ?>
            </strong>
        </div>
    </div>
</div>

<!-- Paneles de Distribución: Métodos de Pago y Planes -->
<div class="reportes-grid-dos">
    
    <!-- Desglose por Método de Pago -->
    <div class="reportes-card reportes-card--grid">
        <div class="reportes-card__header">
            <h3>
                <i class="fa-solid fa-wallet"></i> Métodos de Cobro
            </h3>
            <span class="reportes-card__subtitulo">Ingresos aprobados</span>
        </div>

        <?php if($totalFacturado <= 0): ?>
            <p class="centrado texto-gris">No hay ingresos registrados en este período.</p>
        <?php else: ?>
            <div class="disciplinas-lista">
                <!-- Mercado Pago -->
                <div class="disciplina-item">
                    <div class="disciplina-item__top">
                        <span>
                            <i class="fa-solid fa-credit-card texto-azul"></i> 
                            <strong>Mercado Pago</strong> (<?php echo $kpis['cantMercadoPago']; ?> cobros)
                        </span>
                        <strong>$<?php echo number_format($montoMP, 2, ',', '.'); ?> (<?php echo $pctMP; ?>%)</strong>
                    </div>
                    <div class="barra-progreso">
                        <div class="barra-progreso__fill barra-progreso__fill--presente" style="width: <?php echo $pctMP; ?>%; background: #0284c7;" title="Mercado Pago: $<?php echo number_format($montoMP, 2, ',', '.'); ?>"></div>
                    </div>
                </div>

                <!-- Efectivo -->
                <div class="disciplina-item">
                    <div class="disciplina-item__top">
                        <span>
                            <i class="fa-solid fa-money-bill-1-wave texto-verde"></i> 
                            <strong>Efectivo (Mostrador)</strong> (<?php echo $kpis['cantEfectivo']; ?> cobros)
                        </span>
                        <strong>$<?php echo number_format($montoEf, 2, ',', '.'); ?> (<?php echo $pctEf; ?>%)</strong>
                    </div>
                    <div class="barra-progreso">
                        <div class="barra-progreso__fill barra-progreso__fill--presente" style="width: <?php echo $pctEf; ?>%;" title="Efectivo: $<?php echo number_format($montoEf, 2, ',', '.'); ?>"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recaudación por Plan / Disciplina -->
    <div class="reportes-card reportes-card--grid">
        <div class="reportes-card__header">
            <h3>
                <i class="fa-solid fa-dumbbell"></i> Facturación por Plan
            </h3>
            <span class="reportes-card__subtitulo">Por disciplina</span>
        </div>

        <?php if(empty($porPlan)): ?>
            <p class="centrado texto-gris">No hay transacciones registradas para ningún plan en este período.</p>
        <?php else: ?>
            <div class="disciplinas-lista">
                <?php foreach($porPlan as $pl): 
                    $recaudadoPl = (float)$pl['total_recaudado'];
                    $pctPl = ($totalFacturado > 0) ? round(($recaudadoPl / $totalFacturado) * 100) : 0;
                ?>
                    <div class="disciplina-item">
                        <div class="disciplina-item__top">
                            <strong><?php echo s($pl['plan_nombre']); ?></strong>
                            <span>$<?php echo number_format($recaudadoPl, 2, ',', '.'); ?> (<?php echo (int)$pl['pagos_aprobados']; ?> ventas)</span>
                        </div>
                        <div class="barra-progreso">
                            <div class="barra-progreso__fill barra-progreso__fill--presente" style="width: <?php echo $pctPl; ?>%;" title="<?php echo s($pl['plan_nombre']); ?>: $<?php echo number_format($recaudadoPl, 2, ',', '.'); ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Membresías Próximas a Vencer (Próximos 7 días) -->
<div class="reportes-card" style="margin-bottom: 3rem;">
    <div class="reportes-card__header">
        <div>
            <h3>
                <i class="fa-solid fa-hourglass-end texto-naranja"></i> Membresías por Vencer en los Próximos 7 Días
            </h3>
            <span class="reportes-card__subtitulo">Alumnos que probablemente renueven o requieran cobranza esta semana</span>
        </div>
        <span class="badge <?php echo !empty($proximosVencimientos) ? 'badge--naranja' : 'badge--verde'; ?>">
            <i class="fa-solid fa-bell"></i> <?php echo count($proximosVencimientos); ?> por vencer
        </span>
    </div>

    <?php if(empty($proximosVencimientos)): ?>
        <p class="centrado texto-gris" style="padding: 1.5rem 0;">
            <i class="fa-solid fa-circle-check texto-verde"></i> No hay membresías activas con vencimiento programado en los próximos 7 días.
        </p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>DNI</th>
                        <th>Plan Actual</th>
                        <th>Vencimiento</th>
                        <th>Días Restantes</th>
                        <th class="tabla__th--centro no-print">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($proximosVencimientos as $mv): 
                        $diasRestantes = (int)$mv['dias_restantes'];
                        $telLimpio = !empty($mv['cliente_telefono']) ? preg_replace('/[^0-9]/', '', $mv['cliente_telefono']) : '';
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo s($mv['cliente_nombre']); ?></strong>
                                <small style="display: block; color: #64748b;"><?php echo s($mv['cliente_email']); ?></small>
                            </td>
                            <td>
                                <?php if(!empty($mv['cliente_dni'])): ?>
                                    <span class="tabla-dni"><?php echo s($mv['cliente_dni']); ?></span>
                                <?php else: ?>
                                    <span class="badge-estado badge-estado--sin-dni">Sin DNI</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge--celeste">
                                    <i class="fa-solid fa-dumbbell"></i> <?php echo s($mv['plan_nombre']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo date('d/m/Y', strtotime($mv['fecha_fin'])); ?></strong>
                            </td>
                            <td>
                                <?php if($diasRestantes === 0): ?>
                                    <span class="badge badge--rojo"><i class="fa-solid fa-triangle-exclamation"></i> Vence Hoy</span>
                                <?php elseif($diasRestantes === 1): ?>
                                    <span class="badge badge--naranja"><i class="fa-solid fa-clock"></i> Vence Mañana</span>
                                <?php else: ?>
                                    <span class="badge badge--amarillo"><i class="fa-regular fa-clock"></i> En <?php echo $diasRestantes; ?> días</span>
                                <?php endif; ?>
                            </td>
                            <td class="tabla__td--centro no-print">
                                <div class="acciones-tabla acciones-tabla--centro">
                                    <?php if(!empty($telLimpio)): ?>
                                        <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($mv['cliente_nombre']); ?>%2C%20te%20escribimos%20desde%20el%20gimnasio%20para%20recordarte%20que%20tu%20membres%C3%ADa%20de%20<?php echo urlencode($mv['plan_nombre']); ?>%20est%C3%A1%20pr%C3%B3xima%20a%20vencer." 
                                           target="_blank" 
                                           rel="noopener noreferrer" 
                                           class="btn-micro btn-micro--whatsapp" 
                                           title="Avisar por WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/admin/usuarios/membresia-manual?id=<?php echo $mv['usuario_id']; ?>" 
                                       class="btn-micro btn-micro--editar" 
                                       title="Renovar Membresía Manualmente">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Listado Detallado de Pagos del Período -->
<div class="reportes-card">
    <div class="reportes-card__header">
        <div>
            <h3>
                <i class="fa-solid fa-receipt"></i> Historial de Transacciones y Pagos
            </h3>
            <span class="reportes-card__subtitulo">Mostrando <?php echo count($pagos); ?> transacciones en el período</span>
        </div>
    </div>

    <?php if(empty($pagos)): ?>
        <div class="agenda-vacia-card" style="margin: 2rem 0;">
            <i class="fa-solid fa-receipt icono-muted"></i>
            <h3>No se encontraron pagos</h3>
            <p>No hay registros de transacciones para el período seleccionado (<?php echo s($tituloPeriodo); ?>).</p>
        </div>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Alumno</th>
                        <th>DNI</th>
                        <th>Plan</th>
                        <th>Método</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pagos as $p): 
                        $fechaPagoFormat = !empty($p['fecha_pago']) ? date('d/m/Y H:i', strtotime($p['fecha_pago'])) . ' hs' : '-';
                        $estado = $p['estado'] ?? 'pendiente';
                        $metodo = $p['metodo_pago'] ?? 'mercadopago';
                    ?>
                        <tr>
                            <td>
                                <strong style="color: #334155;"><?php echo $fechaPagoFormat; ?></strong>
                            </td>
                            <td>
                                <a href="/admin/usuarios/detalle?id=<?php echo $p['usuario_id']; ?>" style="text-decoration: none; color: inherit;">
                                    <strong><?php echo s($p['cliente_nombre']); ?></strong>
                                </a>
                                <small style="display: block; color: #64748b;"><?php echo s($p['cliente_email']); ?></small>
                            </td>
                            <td>
                                <?php if(!empty($p['cliente_dni'])): ?>
                                    <span class="tabla-dni"><?php echo s($p['cliente_dni']); ?></span>
                                <?php else: ?>
                                    <span class="badge-estado badge-estado--sin-dni">Sin DNI</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge--celeste">
                                    <i class="fa-solid fa-dumbbell"></i> <?php echo s($p['plan_nombre']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($metodo === 'efectivo'): ?>
                                    <span class="badge badge--verde">
                                        <i class="fa-solid fa-money-bill-wave"></i> Efectivo
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge--azul">
                                        <i class="fa-solid fa-credit-card"></i> Mercado Pago
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size: 1.45rem; color: #0f172a;">
                                    $<?php echo number_format((float)$p['monto'], 2, ',', '.'); ?>
                                </strong>
                            </td>
                            <td>
                                <?php if($estado === 'aprobado'): ?>
                                    <span class="badge badge--verde">
                                        <i class="fa-solid fa-circle-check"></i> Aprobado
                                    </span>
                                <?php elseif($estado === 'pendiente'): ?>
                                    <span class="badge badge--amarillo">
                                        <i class="fa-solid fa-clock"></i> Pendiente
                                    </span>
                                <?php elseif($estado === 'rechazado'): ?>
                                    <span class="badge badge--rojo">
                                        <i class="fa-solid fa-circle-xmark"></i> Rechazado
                                    </span>
                                <?php else: ?>
                                    <span class="badge">
                                        <?php echo s(ucfirst($estado)); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($p['mp_payment_id'])): ?>
                                    <code style="font-size: 1.2rem; background: #f1f5f9; padding: 0.2rem 0.6rem; border-radius: 0.4rem; color: #475569;">
                                        #<?php echo s($p['mp_payment_id']); ?>
                                    </code>
                                <?php elseif($metodo === 'efectivo'): ?>
                                    <span class="texto-gris" style="font-size: 1.2rem;">Cobro mostrador</span>
                                <?php else: ?>
                                    <span class="texto-gris" style="font-size: 1.2rem;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
