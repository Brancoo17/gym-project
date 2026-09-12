<?php
/** @var string $nombre */
/** @var string $status */
?>

<div class="respuesta-pago">
    <?php if($status === 'success' || $status === 'approved'): ?>
        <div class="respuesta-pago__icono respuesta-pago__icono--exito">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h1>¡Pago Exitoso!</h1>
        <p>Tu pago ha sido procesado correctamente. Tu membresía se activará en breve (si no lo hizo ya).</p>
        <a href="/cliente/turnos" class="boton">Ir a mis turnos</a>

    <?php elseif($status === 'pending' || $status === 'in_process'): ?>
        <div class="respuesta-pago__icono respuesta-pago__icono--pendiente">
            <i class="fa-solid fa-clock"></i>
        </div>
        <h1>Pago Pendiente</h1>
        <p>Estamos procesando tu pago. Te avisaremos en cuanto se acredite para activar tu membresía.</p>
        <a href="/cliente/turnos" class="boton">Ir a mis turnos</a>

    <?php elseif($status === 'failure' || $status === 'rejected'): ?>
        <div class="respuesta-pago__icono respuesta-pago__icono--error">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <h1>El pago fue rechazado</h1>
        <p>Hubo un problema al procesar tu pago en Mercado Pago. Por favor, intentá nuevamente con otro medio de pago.</p>
        <a href="/cliente/planes" class="boton">Volver a intentar</a>

    <?php elseif($status === 'ya_activa'): ?>
        <div class="respuesta-pago__icono respuesta-pago__icono--info">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <h1>Membresía Activa</h1>
        <p>Ya tenés una membresía activa para este plan. No es necesario volver a pagar.</p>
        <a href="/cliente/turnos" class="boton">Ir a mis turnos</a>
        
    <?php else: ?>
        <div class="respuesta-pago__icono respuesta-pago__icono--error">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h1>Error al comunicar con Mercado Pago</h1>
        <p>No se pudo generar la orden de pago en este momento.</p>
        <?php if(!empty($_GET['detalle'])): ?>
            <p style="font-size: 1.3rem; color: #ef4444; background: #fee2e2; padding: 1rem; border-radius: 6px; word-break: break-all; margin-bottom: 2rem;">
                <strong>Detalle técnico:</strong> <?php echo s($_GET['detalle']); ?>
            </p>
        <?php endif; ?>
        <a href="/cliente/planes" class="boton">Volver</a>
    <?php endif; ?>
</div>


