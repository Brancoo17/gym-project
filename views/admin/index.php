<?php 
/** @var string $nombre */ 
/** @var array $kpis */
$kpis = $kpis ?? [
    'totalClientes' => 0,
    'totalProfesores' => 0,
    'totalPlanes' => 0,
    'reservasHoy' => 0,
    'presentesHoy' => 0,
    'tasaAsistenciaMes' => 100
];
?>

<div class="dashboard-top">
    <div class="dashboard-top__info">
        <h1>Panel de Administración</h1>
        <p>Hola <?php echo s($nombre); ?>. Monitoreá las operaciones del gimnasio, controlá asistencias y gestioná el negocio.</p>
    </div>
    <div class="dashboard-top__acciones">
        <a href="/admin/reservas" class="boton">
            <i class="fa-solid fa-calendar-check"></i> Agenda de Hoy
        </a>
        <a href="/admin/reportes" class="boton-accion">
            <i class="fa-solid fa-chart-pie"></i> Ver Reportes
        </a>
    </div>
</div>

<!-- Barra de KPIs del Gimnasio en Tiempo Real -->
<div class="agenda-metricas-grid">
    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--azul">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <span class="metrica-card__label">Alumnos Activos</span>
            <strong class="metrica-card__valor"><?php echo $kpis['totalClientes']; ?></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--naranja">
            <i class="fa-solid fa-calendar-day"></i>
        </div>
        <div>
            <span class="metrica-card__label">Reservas de Hoy</span>
            <strong class="metrica-card__valor"><?php echo $kpis['reservasHoy']; ?> <span class="metrica-card__subvalor">(<?php echo $kpis['presentesHoy']; ?> presentes)</span></strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--verde">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div>
            <span class="metrica-card__label">Asistencia del Mes</span>
            <strong class="metrica-card__valor"><?php echo $kpis['tasaAsistenciaMes']; ?>%</strong>
        </div>
    </div>

    <div class="metrica-card">
        <div class="metrica-card__icono metrica-card__icono--gris">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
            <span class="metrica-card__label">Staff de Profesores</span>
            <strong class="metrica-card__valor"><?php echo $kpis['totalProfesores']; ?></strong>
        </div>
    </div>
</div>

<h2 class="dashboard-seccion-titulo">Módulos del Sistema</h2>

<div class="cards-panel">

    <a class="card-panel" href="/admin/usuarios">
        <div class="card-panel__header">
            <h3>Gestión de Clientes</h3>
            <i class="fa-solid fa-address-book icono--azul"></i>
        </div>
        <p>Alta de alumnos en mostrador, activación de cuentas, estado de membresías y seguimiento.</p>
    </a>

    <a class="card-panel" href="/admin/pagos">
        <div class="card-panel__header">
            <h3>Pagos y Facturación</h3>
            <i class="fa-solid fa-credit-card icono--naranja"></i>
        </div>
        <p>Control de ingresos, cobros en mostrador, pagos por Mercado Pago y proyección de vencimientos.</p>
    </a>

    <a class="card-panel" href="/admin/reservas">
        <div class="card-panel__header">
            <h3>Agenda y Asistencia</h3>
            <i class="fa-solid fa-clipboard-user icono--verde"></i>
        </div>
        <p>Control de presentismo en vivo por clase, listado de alumnos y cupos.</p>
    </a>

    <a class="card-panel" href="/admin/reportes">
        <div class="card-panel__header">
            <h3>Métricas y Reportes</h3>
            <i class="fa-solid fa-chart-line icono--azul"></i>
        </div>
        <p>Tasa de presentismo, ausentismo, ranking de alumnos y concurrencia por disciplina.</p>
    </a>

    <a class="card-panel" href="/admin/horarios">
        <div class="card-panel__header">
            <h3>Horarios</h3>
            <i class="fa-solid fa-clock icono--naranja"></i>
        </div>
        <p>Configuración de la grilla semanal, profesores asignados y cupos.</p>
    </a>

    <a class="card-panel" href="/admin/planes">
        <div class="card-panel__header">
            <h3>Planes</h3>
            <i class="fa-solid fa-dumbbell icono--verde"></i>
        </div>
        <p>Alta, edición y fotos WebP de las disciplinas y planes.</p>
    </a>

    <a class="card-panel" href="/admin/entrenadores">
        <div class="card-panel__header">
            <h3>Entrenadores</h3>
            <i class="fa-solid fa-user-group icono--azul"></i>
        </div>
        <p>Alta, edición y gestión del staff de profesores.</p>
    </a>

    <a class="card-panel" href="/admin/rutinas">
        <div class="card-panel__header">
            <h3>Rutinas y WODs</h3>
            <i class="fa-solid fa-chalkboard-user icono--naranja"></i>
        </div>
        <p>Diseño de entrenamientos por plan, WODs diarios y rutinas por alumno.</p>
    </a>

    <a class="card-panel" href="/admin/ejercicios">
        <div class="card-panel__header">
            <h3>Banco de Ejercicios</h3>
            <i class="fa-solid fa-heart-pulse icono--verde"></i>
        </div>
        <p>Catálogo de ejercicios, grupos musculares, técnica y videos.</p>
    </a>

    <a class="card-panel" href="/admin/configuracion">
        <div class="card-panel__header">
            <h3>Configuración del Sitio</h3>
            <i class="fa-solid fa-gears icono--verde"></i>
        </div>
        <p>Personalización de nombre, logo, portada, medios de contacto y redes sociales.</p>
    </a>
</div>

