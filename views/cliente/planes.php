<?php
/** @var string $nombre */
/** @var array $planes */
/** @var array $membresiasActivas */
$planes = $planes ?? [];
$membresiasActivas = $membresiasActivas ?? [];
?>

<div class="planes-header">
    <h1>Contratar Plan</h1>
    <p>Hola <?php echo s($nombre); ?>. Seleccioná el plan que mejor se adapte a tus objetivos.</p>
    
    <?php if(!empty($membresiasActivas)): ?>
        <div class="alerta alerta--exito" style="margin-top: 2rem;">
            <i class="fa-solid fa-circle-check"></i>
            Tenés membresías activas. Solo podés ver las opciones para adquirir planes nuevos.
        </div>
    <?php endif; ?>
</div>

<div class="planes-cards-grid">
    <?php if(empty($planes)): ?>
        <p class="alerta alerta--info">Por el momento no hay planes disponibles.</p>
    <?php else: ?>
        <?php foreach($planes as $plan): 
            $yaContratado = in_array((int)$plan->id, $membresiasActivas);
        ?>
            <div class="card-plan <?php echo $yaContratado ? 'card-plan--activa' : ''; ?>">
                <?php if($plan->imagen): ?>
                    <img class="card-plan__imagen" loading="lazy" src="/imagenes/<?php echo s($plan->imagen); ?>" alt="<?php echo s($plan->nombre); ?>">
                <?php endif; ?>
                
                <div class="card-plan__contenido">
                    <div class="card-plan__header">
                        <h3 class="card-plan__titulo"><?php echo s($plan->nombre); ?></h3>
                    </div>
                    
                    <p class="card-plan__descripcion"><?php echo s($plan->descripcion); ?></p>
                    
                    <ul class="card-plan__lista">
                        <li>
                            <i class="fa-solid fa-calendar-days icono--azul"></i> 
                            Vigencia: <?php echo s($plan->duracion_dias); ?> días
                        </li>
                        <li>
                            <i class="fa-solid fa-users icono--naranja"></i> 
                            Cantidad de Clases: <?php echo empty($plan->cantidad_clases) ? 'Ilimitadas' : s($plan->cantidad_clases); ?>
                        </li>
                    </ul>
                    
                    <p class="card-plan__precio">$<?php echo number_format((float)$plan->precio, 0, ',', '.'); ?></p>
                    
                    <?php if($yaContratado): 
                        $det = $detallesMembresias[(int)$plan->id] ?? null;
                        $diasRest = isset($det['dias_restantes']) ? (int)$det['dias_restantes'] : null;
                        $fechaFin = !empty($det['fecha_fin']) ? date('d/m/Y', strtotime($det['fecha_fin'])) : '';
                    ?>
                        <div style="text-align: center; margin-top: 1rem;">
                            <div class="badge badge--verde" style="padding: 0.8rem 1.2rem; display: flex; align-items: center; justify-content: center; gap: 0.6rem; font-size: 1.45rem; width: 100%;">
                                <i class="fa-solid fa-circle-check"></i> Membresía Activa
                            </div>
                            <?php if(!empty($fechaFin)): ?>
                                <p style="font-size: 1.35rem; color: #475569; margin: 0.8rem 0 0.4rem 0;">
                                    Vigente hasta el <strong><?php echo $fechaFin; ?></strong>
                                </p>
                            <?php endif; ?>
                            <?php if($diasRest !== null && $diasRest >= 0 && $diasRest <= 7): ?>
                                <span class="badge <?php echo $diasRest === 0 ? 'badge--rojo' : 'badge--amarillo'; ?>" style="display: inline-block; margin-top: 0.4rem;">
                                    <i class="fa-solid fa-clock"></i> <?php echo $diasRest === 0 ? '¡Vence hoy!' : ($diasRest === 1 ? 'Vence mañana' : "Vence en {$diasRest} días"); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="/cliente/pago/crear-preferencia" class="card-plan__formulario">
                            <input type="hidden" name="plan_id" value="<?php echo s($plan->id); ?>">
                            <button type="submit" class="boton boton--block" onclick="this.innerHTML='Procesando...'; this.style.opacity='0.7';">
                                <i class="fa-solid fa-credit-card"></i> Contratar con MP
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
