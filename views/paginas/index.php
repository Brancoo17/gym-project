<?php
/** @var array $planes */
/** @var array $horarios */
/** @var bool $enviado */
$configuracion = obtenerConfiguracion();
$tienePortada = (!empty($configuracion->portada) && file_exists(CARPETA_IMAGENES . $configuracion->portada));
?>

<section class="hero">
    <?php if($tienePortada): ?>
        <img class="hero__bg" 
             src="/imagenes/<?php echo s($configuracion->portada); ?>" 
             alt="Portada <?php echo s($configuracion->nombre); ?>" 
             loading="eager" 
             fetchpriority="high">
    <?php endif; ?>
    <div class="contenedor hero__contenido">
        <h1><?php echo s($configuracion->hero_titulo ?: 'Entrená con un plan a tu medida'); ?></h1>
        <p><?php echo s($configuracion->hero_descripcion ?: 'Crossfit, musculación y funcional. Elegí tu membresía y reservá tu turno.'); ?></p>
        <a class="boton" href="/crear-cuenta">Empezar ahora</a>
    </div>
</section>

<section class="seccion" id="planes">
    <div class="contenedor">
        <h2>Planes</h2>
        <p class="seccion__intro">Varias membresías activas al mismo tiempo. Una activa por plan.</p>
        <div class="grid-planes">
            <?php foreach($planes as $plan): ?>
                <article class="card-plan">
                    <?php if($plan->imagen): ?>
                        <img src="/imagenes/<?php echo s($plan->imagen); ?>" alt="<?php echo s($plan->nombre); ?>">
                    <?php endif; ?>
                    <div class="card-plan__info">
                        <h3><?php echo s($plan->nombre); ?></h3>
                        <p><?php echo s($plan->descripcion); ?></p>
                        <p style="font-weight: 500; color: var(--azul);"><i class="fa-solid fa-users"></i> Clases: <?php echo empty($plan->cantidad_clases) ? 'Ilimitadas' : s($plan->cantidad_clases); ?></p>
                        <p class="card-plan__precio">$<?php echo number_format((float)$plan->precio, 0, ',', '.'); ?> / <?php echo s((string)$plan->duracion_dias); ?> días</p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="seccion seccion--alt" id="horarios">
    <div class="contenedor">
        <h2>Cronograma de Clases</h2>
        <p class="seccion__intro">Consultá los horarios semanales por disciplina y elegí el turno que mejor se adapte a tu rutina.</p>
        <?php if(empty($horarios)): ?>
            <p class="centrado texto-gris">Próximamente publicamos la grilla de clases.</p>
        <?php else: ?>
            <?php 
                $esAdmin = false;
                include __DIR__ . '/../templates/grilla-horarios.php'; 
            ?>
        <?php endif; ?>
    </div>
</section>

<section class="seccion" id="ubicacion">
    <div class="contenedor">
        <h2>Ubicación</h2>
        <p><?php echo s($configuracion->direccion ?: 'Av. del Entrenamiento 123, Buenos Aires'); ?></p>
        <div class="mapa">
            <?php 
                $mapaSrc = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3284.0168878895!2d-58.3816!3d-34.6037!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzTCsDM2JzEzLjMiUyA1OMKwMjInNTMuOCJX!5e0!3m2!1ses!2sar!4v1';
                if(!empty($configuracion->mapa_url)) {
                    if(preg_match('/src=["\']([^"\']+)["\']/', $configuracion->mapa_url, $coincidencias)) {
                        $mapaSrc = $coincidencias[1];
                    } else {
                        $mapaSrc = $configuracion->mapa_url;
                    }
                }
            ?>
            <iframe
                title="Ubicación del gimnasio"
                src="<?php echo htmlspecialchars($mapaSrc, ENT_QUOTES, 'UTF-8'); ?>"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<section class="seccion seccion--alt" id="contacto">
    <div class="contenedor">
        <h2>Contacto</h2>
        <p class="seccion__intro">Comunicate con nosotros o encontranos en nuestras redes sociales oficiales.</p>

        <div class="contacto-centro-contenedor">
            <div class="contacto-info-card">
                <h3>¿Tenés alguna duda o consulta?</h3>
                <p>Escribinos por WhatsApp o envianos un correo electrónico. ¡Te esperamos para comenzar a entrenar!</p>

                <div class="contacto-enlaces-lista">
                    <?php if(!empty($configuracion->whatsapp)): ?>
                        <?php 
                            $telLimpio = preg_replace('/[^0-9]/', '', $configuracion->whatsapp); 
                        ?>
                        <a href="https://wa.me/<?php echo $telLimpio; ?>?text=Hola%20<?php echo urlencode($configuracion->nombre); ?>%2C%20quisiera%20consultar%20por%20las%20clases%20y%20planes." 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="contacto-enlace-item contacto-enlace-item--whatsapp">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span>Chatear por WhatsApp (<?php echo s($configuracion->whatsapp); ?>)</span>
                        </a>
                    <?php endif; ?>

                    <div class="contacto-email-fila">
                        <a href="mailto:<?php echo s($configuracion->email); ?>" class="contacto-enlace-item contacto-enlace-item--email">
                            <i class="fa-regular fa-envelope"></i>
                            <span><?php echo s($configuracion->email); ?></span>
                        </a>
                        <button type="button" 
                                class="btn-copiar-email" 
                                onclick="copiarEmail('<?php echo s($configuracion->email); ?>', this)" 
                                title="Copiar correo electrónico">
                            <i class="fa-regular fa-copy"></i>
                            <span>Copiar</span>
                        </button>
                    </div>
                </div>

                <?php if(!empty($configuracion->instagram) || !empty($configuracion->facebook) || !empty($configuracion->tiktok)): ?>
                    <div class="contacto-redes-bloque">
                        <h4 class="contacto-redes-titulo">Nuestras Redes Sociales</h4>
                        <div class="redes-sociales-barra">
                            <?php if(!empty($configuracion->instagram)): ?>
                                <a href="<?php echo str_starts_with($configuracion->instagram, 'http') ? s($configuracion->instagram) : 'https://instagram.com/' . ltrim(s($configuracion->instagram), '@'); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="btn-red-social btn-red-social--instagram" 
                                   title="Instagram">
                                    <i class="fa-brands fa-instagram"></i>
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($configuracion->facebook)): ?>
                                <a href="<?php echo str_starts_with($configuracion->facebook, 'http') ? s($configuracion->facebook) : 'https://facebook.com/' . s($configuracion->facebook); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="btn-red-social btn-red-social--facebook" 
                                   title="Facebook">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($configuracion->tiktok)): ?>
                                <a href="<?php echo str_starts_with($configuracion->tiktok, 'http') ? s($configuracion->tiktok) : 'https://tiktok.com/@' . ltrim(s($configuracion->tiktok), '@'); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="btn-red-social btn-red-social--tiktok" 
                                   title="TikTok">
                                    <i class="fa-brands fa-tiktok"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

