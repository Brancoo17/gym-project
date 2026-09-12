<?php
/** @var \Model\Configuracion $configuracion */
/** @var array $alertas */
/** @var mixed $resultado */
$mensaje = $resultado ? obtenerMensaje((int)$resultado) : '';
?>

<div class="usuarios-header">
    <div class="usuarios-header__info">
        <h1>Configuración del Sitio</h1>
        <p>Personalizá la identidad del gimnasio, logotipo, portada del hero, medios de contacto y redes sociales.</p>
    </div>
    <div class="usuarios-header__acciones">
        <a href="/admin" class="boton-accion">
            <i class="fa-solid fa-arrow-left"></i> Volver al Panel
        </a>
    </div>
</div>

<?php if($mensaje): ?>
    <div class="alerta exito">
        <i class="fa-solid fa-circle-check"></i>
        <span>Configuración <?php echo s($mensaje); ?></span>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/../../templates/alertas.php'; ?>

<form class="config-form-contenedor formulario" method="POST" action="/admin/configuracion" enctype="multipart/form-data">

    <!-- 1. Identidad del Establecimiento -->
    <div class="config-seccion-card">
        <div class="config-seccion-card__header">
            <h3><i class="fa-solid fa-building icono--verde"></i> Identidad del Gimnasio</h3>
            <p>Configurá el nombre principal del establecimiento y el logotipo que identifica a la marca.</p>
        </div>

        <div class="config-seccion-card__cuerpo">
            <div class="campo">
                <label for="nombre">Nombre del Establecimiento <span class="campo-obligatorio">*</span></label>
                <input type="text" 
                       id="nombre" 
                       name="configuracion[nombre]" 
                       value="<?php echo s($configuracion->nombre); ?>" 
                       placeholder="Ej. Iron Gym, Crossfit Box, Sparta Fitness" 
                       required 
                       maxlength="60">
                <span class="campo-ayuda">
                    <i class="fa-solid fa-circle-info"></i> Este nombre reemplazará el título de la pestaña del navegador, encabezados, pie de página y remitente de correos.
                </span>
            </div>

            <div class="campo">
                <label>Logotipo del Gimnasio</label>
                <div class="config-media-layout">
                    <div class="config-preview-box config-preview-box--logo" id="box_preview_logo">
                        <?php if(!empty($configuracion->logo)): ?>
                            <img src="/imagenes/<?php echo s($configuracion->logo); ?>" alt="Logo actual" id="img_preview_logo">
                            <span class="config-preview-box__badge">Actual</span>
                        <?php else: ?>
                            <div class="config-preview-box__placeholder" id="placeholder_logo">
                                <i class="fa-solid fa-image"></i>
                                <span>Sin logo cargado</span>
                            </div>
                            <img src="" alt="Vista previa logo" id="img_preview_logo" class="config-preview-box__img-oculta">
                        <?php endif; ?>
                    </div>

                    <div class="config-media-controles">
                        <input type="file" 
                               id="input_logo" 
                               name="logo" 
                               accept="image/png, image/jpeg, image/webp" 
                               onchange="previsualizarImagen(this, 'img_preview_logo', 'placeholder_logo')">

                        <div class="config-badge-recomendacion">
                            <i class="fa-solid fa-lightbulb"></i>
                            <div>
                                <strong>Recomendación:</strong> Formato <strong>PNG</strong> o <strong>WEBP</strong> con fondo transparente. Tamaño recomendado: <strong>500x500 px</strong> o proporción horizontal.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Portada y Presentación (Hero) -->
    <div class="config-seccion-card config-seccion-card--azul">
        <div class="config-seccion-card__header">
            <h3><i class="fa-solid fa-panorama icono--azul"></i> Portada y Presentación (Hero)</h3>
            <p>Personalizá el mensaje de bienvenida, título principal y la imagen de fondo de la portada de inicio.</p>
        </div>

        <div class="config-seccion-card__cuerpo">
            <div class="campo">
                <label for="hero_titulo">Título Principal del Hero</label>
                <input type="text" 
                       id="hero_titulo" 
                       name="configuracion[hero_titulo]" 
                       value="<?php echo s($configuracion->hero_titulo); ?>" 
                       placeholder="Ej. Entrená con un plan a tu medida" 
                       maxlength="150">
                <span class="campo-ayuda">
                    <i class="fa-solid fa-heading"></i> Encabezado de bienvenida con máximo impacto visual sobre el banner del sitio.
                </span>
            </div>

            <div class="campo">
                <label for="hero_descripcion">Descripción o Bajada del Hero</label>
                <input type="text" 
                       id="hero_descripcion" 
                       name="configuracion[hero_descripcion]" 
                       value="<?php echo s($configuracion->hero_descripcion); ?>" 
                       placeholder="Ej. Crossfit, musculación y funcional. Elegí tu membresía y reservá tu turno." 
                       maxlength="255">
                <span class="campo-ayuda">
                    <i class="fa-solid fa-align-left"></i> Frase secundaria o subtítulo descriptivo de los servicios del gimnasio.
                </span>
            </div>

            <div class="campo">
                <label for="input_portada">Foto de Fondo (Banner)</label>
                <div class="config-media-layout">
                    <div class="config-preview-box config-preview-box--portada" id="box_preview_portada">
                        <?php if(!empty($configuracion->portada)): ?>
                            <img src="/imagenes/<?php echo s($configuracion->portada); ?>" alt="Portada actual" id="img_preview_portada">
                            <span class="config-preview-box__badge">Actual</span>
                        <?php else: ?>
                            <div class="config-preview-box__placeholder" id="placeholder_portada">
                                <i class="fa-solid fa-dumbbell"></i>
                                <span>Sin foto de portada</span>
                            </div>
                            <img src="" alt="Vista previa portada" id="img_preview_portada" class="config-preview-box__img-oculta">
                        <?php endif; ?>
                    </div>

                    <div class="config-media-controles">
                        <input type="file" 
                               id="input_portada" 
                               name="portada" 
                               accept="image/png, image/jpeg, image/webp" 
                               onchange="previsualizarImagen(this, 'img_preview_portada', 'placeholder_portada')">

                        <div class="config-badge-recomendacion">
                            <i class="fa-solid fa-circle-info"></i>
                            <div>
                                <strong>Recomendación de tamaño:</strong> <strong>1920x1080 px</strong> o superior (formato horizontal apaisado 16:9, máx 5MB). Se integrará con un elegante degradado oscuro para que el texto siempre sea legible.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Métodos de Contacto -->
    <div class="config-seccion-card config-seccion-card--naranja">
        <div class="config-seccion-card__header">
            <h3><i class="fa-solid fa-headset icono--naranja"></i> Métodos de Contacto</h3>
            <p>Información de atención al público y dirección de correo institucional para envíos automáticos.</p>
        </div>

        <div class="config-seccion-card__cuerpo">
            <div class="campo">
                <label for="email">Email de Contacto <span class="campo-obligatorio">*</span></label>
                <input type="email" 
                       id="email" 
                       name="configuracion[email]" 
                       value="<?php echo s($configuracion->email); ?>" 
                       placeholder="contacto@tugimnasio.com" 
                       required 
                       maxlength="60">
                <span class="campo-ayuda">
                    <i class="fa-solid fa-triangle-exclamation"></i> <strong>Obligatorio:</strong> Este email se utilizará como remitente de todos los correos del sistema (confirmar cuenta, restablecer contraseña y avisos de membresías).
                </span>
            </div>

            <div class="campo">
                <label for="whatsapp">WhatsApp de Contacto</label>
                <input type="text" 
                       id="whatsapp" 
                       name="configuracion[whatsapp]" 
                       value="<?php echo s($configuracion->whatsapp); ?>" 
                       placeholder="Ej. 5491123456789" 
                       maxlength="20">
                <span class="campo-ayuda">
                    <i class="fa-brands fa-whatsapp"></i> Ingresá el número con código de país y área sin espacios ni signos (+). Se usará para el botón de chat directo en la web.
                </span>
            </div>
        </div>
    </div>

    <!-- 4. Redes Sociales -->
    <div class="config-seccion-card config-seccion-card--morado">
        <div class="config-seccion-card__header">
            <h3><i class="fa-solid fa-share-nodes icono--morado"></i> Redes Sociales</h3>
            <p>Enlaces a los perfiles oficiales del gimnasio para mostrar en la web y pie de página.</p>
        </div>

        <div class="config-seccion-card__cuerpo">
            <div class="campo">
                <label for="instagram">Instagram</label>
                <div class="campo-red-social">
                    <div class="campo-red-social__icono campo-red-social__icono--instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </div>
                    <input type="text" 
                           id="instagram" 
                           name="configuracion[instagram]" 
                           class="campo-red-social__input" 
                           value="<?php echo s($configuracion->instagram); ?>" 
                           placeholder="https://instagram.com/tugimnasio o @usuario" 
                           maxlength="100">
                </div>
            </div>

            <div class="campo">
                <label for="facebook">Facebook</label>
                <div class="campo-red-social">
                    <div class="campo-red-social__icono campo-red-social__icono--facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </div>
                    <input type="text" 
                           id="facebook" 
                           name="configuracion[facebook]" 
                           class="campo-red-social__input" 
                           value="<?php echo s($configuracion->facebook); ?>" 
                           placeholder="https://facebook.com/tugimnasio" 
                           maxlength="100">
                </div>
            </div>

            <div class="campo">
                <label for="tiktok">TikTok</label>
                <div class="campo-red-social">
                    <div class="campo-red-social__icono campo-red-social__icono--tiktok">
                        <i class="fa-brands fa-tiktok"></i>
                    </div>
                    <input type="text" 
                           id="tiktok" 
                           name="configuracion[tiktok]" 
                           class="campo-red-social__input" 
                           value="<?php echo s($configuracion->tiktok); ?>" 
                           placeholder="https://tiktok.com/@tugimnasio" 
                           maxlength="100">
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Ubicación del Establecimiento -->
    <div class="config-seccion-card config-seccion-card--rojo">
        <div class="config-seccion-card__header">
            <h3><i class="fa-solid fa-location-dot icono--rojo"></i> Ubicación del Establecimiento</h3>
            <p>Dirección física y mapa interactivo de Google Maps que se mostrará en la página principal.</p>
        </div>

        <div class="config-seccion-card__cuerpo">
            <div class="campo">
                <label for="direccion">Dirección Física</label>
                <input type="text" 
                       id="direccion" 
                       name="configuracion[direccion]" 
                       value="<?php echo s($configuracion->direccion); ?>" 
                       placeholder="Ej. Av. del Entrenamiento 123, Buenos Aires" 
                       maxlength="100">
                <span class="campo-ayuda">
                    <i class="fa-solid fa-map-pin"></i> Esta dirección se muestra como texto principal en la sección de ubicación del sitio público.
                </span>
            </div>

            <div class="campo">
                <label for="mapa_url">Enlace o Código de Google Maps</label>
                <textarea id="mapa_url" 
                          name="configuracion[mapa_url]" 
                          rows="3" 
                          placeholder="Pegá aquí el enlace del mapa (URL https://... o la etiqueta <iframe> de inserción completa)"><?php echo s($configuracion->mapa_url); ?></textarea>
                <span class="campo-ayuda">
                    <i class="fa-solid fa-circle-info"></i> En Google Maps seleccioná: <em>Compartir > Insertar un mapa > Copiar HTML</em>. Podés pegar el iframe completo o solo el enlace de la propiedad src.
                </span>
            </div>
        </div>
    </div>

    <!-- 6. Módulos y Servicios Activos -->
    <div class="config-seccion-card config-seccion-card--morado">
        <div class="config-seccion-card__header">
            <h3><i class="fa-solid fa-sliders icono--morado"></i> Módulos y Servicios Activos</h3>
            <p>Activá o desactivá funciones según el modelo de negocio del gimnasio (musculación tradicional, box de crossfit, o centro integral).</p>
        </div>

        <div class="config-seccion-card__cuerpo">
            <div class="config-modulos-grid">
                <!-- Módulo Turnos y Reservas -->
                <div class="config-modulo-card">
                    <div class="config-modulo-card__info">
                        <h4><i class="fa-solid fa-calendar-check"></i> Sistema de Reservas y Turnos con Cupo</h4>
                        <p>Habilita la grilla de horarios semanales y permite a los alumnos reservar turnos para sus clases. Si el gimnasio es exclusivamente de sala de musculación o pase libre sin turnos horarios, podés desactivarlo para simplificar el menú de los alumnos y administradores.</p>
                    </div>
                    <div class="config-modulo-card__control">
                        <label class="switch-toggle" for="switch_turnos" title="Activar / Desactivar Turnos">
                            <input type="hidden" name="configuracion[habilitar_turnos]" value="0">
                            <input type="checkbox" id="switch_turnos" name="configuracion[habilitar_turnos]" value="1" <?php echo !empty($configuracion->habilitar_turnos) ? 'checked' : ''; ?>>
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Módulo Crossfit y WODs -->
                <div class="config-modulo-card">
                    <div class="config-modulo-card__info">
                        <h4><i class="fa-solid fa-stopwatch"></i> Metodología Crossfit / WODs</h4>
                        <p>Habilita el constructor con parámetros WOD (AMRAP, EMOM, Time Cap) y bloques Core, Warmup, Fuerza y WOD. Si está desactivado, el sistema adapta todos los paneles para trabajar con <strong>"Rutinas de Entrenamiento"</strong> tradicionales organizadas por días y grupos musculares.</p>
                    </div>
                    <div class="config-modulo-card__control">
                        <label class="switch-toggle" for="switch_crossfit" title="Activar / Desactivar Crossfit">
                            <input type="hidden" name="configuracion[habilitar_crossfit]" value="0">
                            <input type="checkbox" id="switch_crossfit" name="configuracion[habilitar_crossfit]" value="1" <?php echo !empty($configuracion->habilitar_crossfit) ? 'checked' : ''; ?>>
                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Guardar -->
    <div class="config-submit-bar">
        <div class="config-submit-bar__info">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Los cambios guardados se aplicarán de inmediato en todo el sitio web.</span>
        </div>
        <button type="submit" class="boton">
            <i class="fa-solid fa-floppy-disk"></i> Guardar Configuración
        </button>
    </div>
</form>

