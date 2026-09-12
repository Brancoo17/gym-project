<?php /** @var string $nombre */ ?>

<h1>Panel de entrenador</h1>
<p>Hola <?php echo s($nombre); ?>. Acá vas a ver tus alumnos, rutinas, clases y asistencia.</p>

<div class="cards-panel">
    <a class="card-panel" href="/entrenador/asistencia">
        <div class="card-panel__header">
            <h3>Clases y Control de Asistencia</h3>
            <i class="fa-solid fa-clipboard-user icono--verde"></i>
        </div>
        <p>Pasar lista a los alumnos inscriptos en tus clases de hoy y registrar presentismo.</p>
    </a>

    <a class="card-panel" href="/entrenador/alumnos">
        <div class="card-panel__header">
            <h3>Alumnos</h3>
            <i class="fa-solid fa-users icono--azul"></i>
        </div>
        <p>Consultá la lista de alumnos que asisten a tus clases y el seguimiento de sus entrenamientos.</p>
    </a>

    <a class="card-panel" href="/admin/rutinas">
        <div class="card-panel__header">
            <h3>Rutinas y WODs</h3>
            <i class="fa-solid fa-chalkboard-user icono--naranja"></i>
        </div>
        <p>Armar y publicar entrenamientos diarios, WODs de Crossfit y planes personalizados.</p>
    </a>

    <a class="card-panel" href="/admin/ejercicios">
        <div class="card-panel__header">
            <h3>Banco de Ejercicios</h3>
            <i class="fa-solid fa-heart-pulse icono--verde"></i>
        </div>
        <p>Consultar y registrar ejercicios, técnicas y grupos musculares.</p>
    </a>
</div>
