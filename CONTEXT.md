# CONTEXT.md — Guía y Estado del Proyecto Gym MVC

Documento de referencia para agentes de IA y desarrolladores. Diseñado para ser escaneable, conciso y representar fielmente la arquitectura, modelo de datos, estado de avance y convenciones de trabajo.

---

## 1. Qué es el proyecto

Sistema integral de gestión de gimnasio desarrollado en **PHP con arquitectura MVC propia (sin frameworks externos)**. Sigue el mismo stack, estructura y convenciones utilizadas en los proyectos del desarrollador (_AppSalon, Ecommerce, BienesRaices_ — GitHub: `Brancoo17`).

---

## 2. Stack y arquitectura (no negociable)

- **Backend**: PHP 8+ MVC propio viviendo en la **raíz del proyecto** (sin carpeta `app/`).
- **Base de Datos**: MySQL. El archivo [sql/gym_mvc.sql](file:///c:/Users/Branco/Desktop/gym-project/sql/gym_mvc.sql) es la **fuente de verdad** única del modelo (las tablas ya están creadas en la base de datos `gym_mvc`).
- **Frontend & Estilos**: SCSS (SASS) modular con metodología BEM + Gulp compilando automáticamente a `public/build/css/app.css`. **Todos los estilos deben escribirse obligatoriamente en archivos `.scss` (en `src/scss/`)**. JavaScript Vanilla nativo utilizando `fetch()` para peticiones asíncronas/AJAX.
- **JavaScript & Modularización**: Todo el código JavaScript del frontend se centraliza obligatoriamente en `src/js/app.js` y se compila con Gulp a `public/build/js/app.js`. **Cualquier código JS que no requiera variables o directivas PHP incrustadas debe ubicarse en `src/js/app.js`**, organizado con funciones modulares, comentarios delimitando la vista a la que pertenece y cláusulas de guarda (_guard clauses_) para verificar la presencia de elementos en el DOM. Las vistas PHP no deben contener etiquetas `<script>` salvo para inyectar estructuras dinámicas provistas exclusivamente por PHP en el servidor (ej. arrays JSON inyectados).
- **Enrutamiento**: `Router.php` como despachador y `public/index.php` como Front Controller.
- **Autoload & PSR-4**: Composer configurado con namespaces `MVC\`, `Controllers\`, `Model\`, `Classes\`.
- **Capa de Datos**: Patrón `ActiveRecord` propio en `models/ActiveRecord.php` (`all()`, `find()`, `where()`, `SQL()`, `guardar()`, `sincronizar()`, `alertas()`) — sin ORMs externos.
- **Bootstrap y Helpers**: `includes/app.php`, `database.php` y `funciones.php` con funciones helper (`s()`, `debuguear()`, `isAuth()`, `isAdmin()`, `isEntrenador()`).
- **Motor de Vistas**: Renderizado mediante `$router->render('area/vista', [...])` con layouts en `views/layout.php`.
- **Dependencias Composer**: Limitadas a herramientas puntuales: `phpmailer/phpmailer`, `intervention/image` y `mercadopago/dx-php`.
- **Seguridad y Lógica Crítica**: Validaciones sensibles (precios, cupos disponibles, fechas y estados de membresía) validadas **siempre en el servidor (server-side)**.

---

## 3. Qué NO hacer

- ❌ **NO escribir estilos inline** (`style="..."`) en las vistas HTML/PHP. **Todo estilo de maquetación, layout, espaciado, colores, tipografía y diseño debe realizarse pura y exclusivamente en archivos `.scss` (SASS) compilados por Gulp**. La única excepción admisible son anchos dinámicos de barras de progreso basados en datos calculados en PHP/JS (`style="width: X%;"`).
- ❌ **NO escribir código JavaScript en las vistas HTML/PHP si no requiere código PHP incrustado**. Todo script de interacción, eventos, filtros, modales, animaciones y lógica del frontend debe ubicarse obligatoriamente en `src/js/app.js` (modularizado con comentarios explicativos por vista y guard clauses) y compilarse con Gulp a `public/build/js/app.js`. Las únicas excepciones admisibles son variables o estructuras de datos dinámicas generadas directamente por PHP en el servidor (ej. arrays JSON inyectados en la vista).
- ❌ **NO** introducir frameworks de PHP (Laravel, Symfony, CodeIgniter, etc.) ni frameworks de JavaScript (React, Vue, Tailwind, etc.).
- ❌ **NO** renombrar carpetas, mover la estructura ni alterar las convenciones establecidas sin previa autorización justificada.
- ❌ **NO** reescribir desde cero componentes o módulos existentes; construir y extender siempre sobre la base funcional.
- ❌ **NO** generar ni modificar código sin solicitud explícita del desarrollador para ese paso puntual.
- ❌ **NO** saltarse etapas ni encadenar múltiples pasos grandes de golpe sin confirmación y validación previa.

---

## 4. Modelo de datos (resumen)

### Entidades Principales

| Entidad                 | Tabla                 | Propósito y Relaciones Clave                                                                                               |
| :---------------------- | :-------------------- | :------------------------------------------------------------------------------------------------------------------------- |
| **Usuarios**            | `usuarios`            | Cuentas del sistema con DNI, roles (`cliente`, `admin`, `entrenador`), credenciales, token y confirmación.               |
| **Planes**              | `planes`              | Catálogo de disciplinas/planes (precio, duración en días, cupo opcional, imagen, activo).                                  |
| **Membresías**          | `membresias`          | Suscripciones de clientes a planes con estados (`pendiente`, `activa`, `vencida`, `cancelada`) y vigencia.                 |
| **Pagos**               | `pagos`               | Registro de transacciones monetarias (Mercado Pago y efectivo en mostrador), estados, método de pago (`mercadopago`, `efectivo`), fecha de pago y sincronización con MP (`mp_preference_id`, `mp_payment_id`, `mp_status`). |
| **Entrenador-Clientes** | `entrenador_clientes` | Asignación directa y seguimiento de alumnos por entrenador.                                                                |
| **Horarios**            | `horarios`            | Grilla de turnos semanales por plan, entrenador, día de semana (0-6), horas y cupo.                                        |
| **Reservas**            | `reservas`            | Turnos reservados por alumnos en horarios específicos con estados (`reservada`, `cancelada`).                              |
| **Asistencias**         | `asistencias`         | Control de presentismo diario (`presente` 0/1) vinculado a la reserva y registrado por un usuario.                         |
| **Ejercicios**          | `ejercicios`          | Catálogo de ejercicios con grupo muscular, multimedia y videos de técnica.                                                 |
| **Rutinas**             | `rutinas`             | Cabecera de rutina para un plan o alumno individual (`cliente_id` nullable), formatos estándar o WOD.                      |
| **Rutina-Ejercicios**   | `rutina_ejercicios`   | Detalle estructurado: bloques (Core, Warmup, Fuerza, WOD), series, reps, pesos (♂/♀), rondas y notas.                      |
| **Avisos Enviados**     | `avisos_enviados`     | Historial de alertas de vencimiento enviadas automáticamente (`7dias`, `hoy`).                                             |

> **Regla de Negocio Crítica**: Un usuario puede tener múltiples membresías activas simultáneamente (ej. Musculación + Crossfit), pero **como máximo una membresía activa por par `(usuario_id, plan_id)`**. Esto debe validarse siempre en el backend.

---

## 5. Roles y flujos

### Roles del Sistema

- **Admin**: Acceso absoluto. Administra planes, horarios, usuarios, entrenadores, catálogos, reportes globales, métricas y asistencias.
- **Entrenador**: Gestiona horarios asignados, visualiza inscriptos, pasa lista / presentismo diario, carga y asigna rutinas grupales o individuales (WODs, circuitos y planes de musculación).
- **Cliente (Alumno)**: Visualiza catálogo de planes, gestiona sus reservas de clases semanales, consulta su pizarra de rutinas (Whiteboard interactivo), revisa su asistencia e historial de membresías.

### Flujo de Pago (Mercado Pago)

1. El cliente selecciona un plan en el sistema.
2. El backend genera una membresía en estado `pendiente` y crea una preferencia de pago en Mercado Pago (Checkout Pro).
3. El cliente abona en la pasarela segura de Mercado Pago.
4. Mercado Pago notifica el resultado mediante **Webhooks / IPN** al endpoint del backend.
5. El backend valida el pago, registra el registro en `pagos` y **activa automáticamente la membresía** calculando las fechas de vigencia.

---

## 6. Plan de etapas y estado actual

| Etapa  | Descripción                                                                                                                                                                                                                                      |      Estado       |
| :----: | :----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :---------------: |
| **1**  | **Arquitectura Base, Autenticación y Roles**: MVC, DB, Login/Registro, Confirmación por Email, Recuperación de clave.                                                                                                                            |   ✅ Completada   |
| **2**  | **Gestión de Planes y Membresías**: CRUD de planes, precios, duraciones, activación y asignación de planes.                                                                                                                                      |   ✅ Completada   |
| **3**  | **Gestión de Horarios y Cupos**: Grilla de turnos, días, horarios, entrenadores y límites de capacidad.                                                                                                                                          |   ✅ Completada   |
| **4**  | **Sistema de Reservas y Turnos**: Reserva semanal interactiva por alumnos, validaciones de cupo en tiempo real y panel de ocupación.                                                                                                             |   ✅ Completada   |
| **5**  | **Ejercicios, Rutinas y Pizarra (WOD)**: Catálogo de ejercicios, constructor dinámico (CrossFit, Funcional, Musculación con distinción hombre/mujer o alumno individual), buscador en tiempo real y Whiteboard interactivo.                      |   ✅ Completada   |
| **6**  | **Control de Asistencia y Reportes**: Presentismo diario para admin/entrenador, historial de asistencias del alumno y métricas/dashboard de negocio.                                                                                             |   ✅ Completada   |
| **7**  | **Optimización y Experiencia Visual de Calendario/Reservas**: Selector de días tipo tabs (mobile-first), filtros dinámicos en tiempo real (por disciplina/turno) e indicadores visuales de cupos con SASS y Vanilla JS (sin librerías externas). | ✅ **Completada** |
| **8**  | **Teoría y Aprendizaje de Pasarelas de Pago**: Fundamentos de APIs de pago, Checkout Pro vs Transparente, tokens y webhooks.                                                                                                                     |   ✅ Completada   |
| **9**  | **Implementación Práctica de Pagos y Sistema de membresías**: Integración con Mercado Pago SDK, generación de preferencias, webhooks y activación automática. Implementar sistema de membresías.                                                 |   ✅ Completada   |
| **10** | **Cron Jobs, Automatización y Cierre**: Tareas programadas para vencimientos y recordatorios, auditoría de seguridad y puesta a punto final.                                                                                                     | ✅ **Completada** |

### Resumen de Implementación de la Etapa 10 (Completada)

- **Paso 1: Modelo `AvisoEnviado` y Plantillas de Correo para Vencimientos**:
  - Modelo [`models/AvisoEnviado.php`](file:///c:/Users/Branco/Desktop/gym-project/models/AvisoEnviado.php) conectado a la tabla `avisos_enviados` con métodos `yaEnviado()` y `registrar()`.
  - Extensión de [`classes/Email.php`](file:///c:/Users/Branco/Desktop/gym-project/classes/Email.php) con plantillas HTML corporativas para recordatorio a 7 días (`enviarAvisoVencimientoProximo()`) y vencimiento en el día (`enviarAvisoVencimientoHoy()`).
- **Paso 2: Script CLI / Cron Job Automatizado (`scripts/cron-membresias.php`)**:
  - Archivo ejecutable por consola o programador de tareas del sistema operativo ([`scripts/cron-membresias.php`](file:///c:/Users/Branco/Desktop/gym-project/scripts/cron-membresias.php)).
  - Tarea 1: Caducidad automática de membresías (`estado = 'activa'` con `fecha_fin < CURDATE()`) pasando a `'vencida'`.
  - Tarea 2: Aviso preventivo por email a 7 días exactos del vencimiento, evitando correos duplicados con `avisos_enviados`.
  - Tarea 3: Aviso de vencimiento el día de hoy por email con registro de notificación única.
  - Modo simulación con `--dry-run` para pruebas sin alterar datos ni disparar correos.
  - Pausa controlada entre envíos (`sleep(1)`) para respetar cuotas por segundo de servidores SMTP (Mailtrap / Gmail).
- **Paso 3: Alertas Visuales en Paneles de Alumno y Administración**:
  - Panel Alumno ([`views/cliente/index.php`](file:///c:/Users/Branco/Desktop/gym-project/views/cliente/index.php)): Badges informativos con fecha de vigencia y alertas dinámicas (`Vence en X días`, `Vence hoy`), más banner preventivo destacado para renovar con anticipación.
  - Catálogo de Planes ([`views/cliente/planes.php`](file:///c:/Users/Branco/Desktop/gym-project/views/cliente/planes.php)): Indicador de vigencia y alertas de vencimiento para membresías ya activas.
  - Panel Admin ([`controllers/UsuarioController.php`](file:///c:/Users/Branco/Desktop/gym-project/controllers/UsuarioController.php) y [`views/admin/usuarios/detalle.php`](file:///c:/Users/Branco/Desktop/gym-project/views/admin/usuarios/detalle.php)): Exclusión inmediata de membresías con `fecha_fin < CURDATE()` en métricas y badges de días restantes en la ficha individual del alumno.
- **Paso 4: Auditoría de Seguridad, Permisos de Roles y Sanitización**:
  - 100% de las 80 rutas auditadas y verificadas con cláusulas de guarda (`isAdmin()`, `isEntrenador()`, `isCliente()`, `isAuth()`).
  - Subida de archivos restringida a imágenes procesadas por `Intervention Image` re-codificadas a WebP con hashes aleatorios.
  - Hardening con `.htaccess` en `public/` (`Options -Indexes`), en `public/imagenes/` (bloqueo total de ejecución de scripts PHP) y en la raíz del proyecto (bloqueo de archivos `.env`, `.git`, `sql/` y redirección a `public/`).
- **Paso 5: Unificación en `.env` y Despliegue en Producción (InfinityFree + Gmail SMTP) [COMPLETADO]**:
  - Centralización completa de credenciales de base de datos (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`) y correo SMTP (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, `MAIL_FROM`, `MAIL_ENCRYPTION`) en `.env` y `.env.example`.
  - Integración de Gmail SMTP (puerto 587 TLS) con Contraseña de Aplicación de Google para entrega confiable de correos en producción.
  - Compatibilidad de rutas para imágenes y assets en hosting compartido (`htdocs/` vs `public/`) con definición dinámica y robusta de `CARPETA_IMAGENES` en `includes/funciones.php`.
  - Despliegue en vivo en InfinityFree bajo el dominio: `https://gym-fitness.freedev.app/`.
  - Base de datos MySQL poblada con datos de demostración (usuarios con diferentes roles, planes, horarios, ejercicios WebP y rutinas).
  - Decodificación binaria en memoria con `file_get_contents()` y `BinaryImageDecoder` en todos los controladores de subida (`ConfiguracionController`, `PlanController`, `EjercicioController`) para garantizar total compatibilidad con carpetas temporales protegidas en hosting compartido (`/home/uploads`), con manejo robusto de excepciones `try/catch`.
  - Script de cron jobs (`scripts/cron-membresias.php`) disponible y testeado para programar en cPanel o servicios externos cuando se requiera.

### Resumen de Implementación de la Etapa 7 (Completada)

- **Paso 1: Selector de Día por Pestañas / Tabs Semanales**:
  - Carrusel horizontal interactivo de 14 días hábiles (lunes a sábado) con navegación táctil, wheel scroll y botones de flecha en [`views/cliente/reservar.php`](file:///c:/Users/Branco/Desktop/gym-project/views/cliente/reservar.php).
  - Selector de fecha manual libre con validación preventiva de fechas pasadas y aviso amigable de gimnasio cerrado los domingos.
  - Sincronización horaria exacta con la zona horaria de Argentina (`America/Argentina/Buenos_Aires`).
  - Bloqueo en backend y frontend para impedir reservar o cancelar turnos pasados del día en curso.
- **Paso 2: Filtros Dinámicos de Disciplina y Franja Horaria**:
  - Barra de filtros tipo "pills" en tiempo real por disciplina (todas, crossfit, etc.) y turno (mañana `< 12 hs`, tarde `12 a 18 hs`, noche `> 18 hs`).
  - Regla especial de permanencia para Musculación (horario de corrido 08:00 a 23:00 hs) para mostrarse en todas las franjas horarias sin romperse.
  - Contador reactivo de resultados y tarjeta de estado vacío cuando ningún turno coincide con los filtros aplicados.
- **Paso 3: Indicadores Visuales de Cupos y Disponibilidad**:
  - Barra de progreso dinámica en cada turno con gradiente semántico según porcentaje de ocupación (verde `< 65%`, naranja `>= 65%` o `<= 3` lugares, rojo `100%` lleno).
  - Tratamiento especial para clases de Musculación / Sala Libre: visualización de cupo libre con icono de infinito (`fa-infinity`) sin barra de porcentaje artificial.
  - Badges de estado en tiempo real: _Disponible_, _Últimos lugares_, _Completo_, _Tu Turno_ y _Finalizado_.
- **Paso 4: Micro-interacciones, Feedback de Reserva y Modal de Cancelación**:
  - Modal moderno de confirmación de cancelación de turno con `backdrop-filter: blur(4px)` unificado en `views/cliente/reservar.php` y `views/cliente/turnos.php`.
  - Desvanecimiento y remoción animada de la tarjeta cancelada actualizando métricas en tiempo real.
  - Alerta tipo toast con barra de progreso temporizadora de 7 segundos y animación de salida.
  - Grilla de tarjetas skeleton shimmer durante la carga asíncrona de turnos.
- **Refactorización y Centralización de JavaScript en `src/js/app.js`**:
  - Migración completa de todo el código JS de las vistas PHP hacia [`src/js/app.js`](file:///c:/Users/Branco/Desktop/gym-project/src/js/app.js) compilado con Gulp a `public/build/js/app.js`.
  - Organización en 14 módulos independientes con cláusulas de guarda (_guard clauses_) y comentarios delimitando claramente la vista correspondiente (Sidebar, Reservas, Turnos, Grilla de Horarios, Copiar Email, Admin Entrenadores, Admin Ejercicios, Admin Usuarios, Admin Configuración, Admin Horarios, Admin Reservas, Entrenador Asistencia, Entrenador Alumnos, Rutinas Detalle WOD).
  - Limpieza de scripts inline de las vistas manteniendo desacopladas las fechas dinámicas mediante atributos HTML5 `data-*`.

### Resumen de Implementación de la Etapa 6

- **Paso 1**:
  - Modelo `Asistencia` ([models/Asistencia.php](file:///c:/Users/Branco/Desktop/gym-project/models/Asistencia.php)) conectado con tabla `asistencias`.
  - Endpoint `POST /api/asistencias/marcar` en [controllers/APIController.php](file:///c:/Users/Branco/Desktop/gym-project/controllers/APIController.php) con control de permisos para admin y entrenador de la clase.
- **Paso 2**:
  - Controles de presentismo interactivo (Presente / Ausente) en la agenda diaria del administrador ([views/admin/reservas/index.php](file:///c:/Users/Branco/Desktop/gym-project/views/admin/reservas/index.php)).
  - Panel mobile-first de asistencia para el entrenador ([views/entrenador/asistencia.php](file:///c:/Users/Branco/Desktop/gym-project/views/entrenador/asistencia.php)) con métricas del día y enlace directo a WhatsApp.
- **Paso 3**:
  - Vista del alumno ([views/cliente/turnos.php](file:///c:/Users/Branco/Desktop/gym-project/views/cliente/turnos.php)) con KPIs de presentismo personal, badges de asistencia (Presente / Ausente / Cancelada / Sin registrar) y bloqueo de cancelación si la asistencia ya fue confirmada.
  - Dashboard del administrador ([views/admin/index.php](file:///c:/Users/Branco/Desktop/gym-project/views/admin/index.php)) con KPIs en tiempo real (alumnos activos, reservas hoy, presentes y tasa del mes).
  - Agenda del administrador ([views/admin/reservas/index.php](file:///c:/Users/Branco/Desktop/gym-project/views/admin/reservas/index.php)) con contadores de presentes y porcentaje de asistencia de la jornada.
  - Módulo completo de reportes y métricas de negocio ([views/admin/reportes.php](file:///c:/Users/Branco/Desktop/gym-project/views/admin/reportes.php)) con filtros de período (mes actual, mes anterior, últimos 30 días, año y completo), tasa de presentismo/ausentismo, concurrencia por disciplina, ranking top 10 alumnos más fieles y versión optimizada para imprimir.

### Ajustes y Módulos Previos a la Etapa 7 (Completados)

- **Acceso del Cliente al Banco de Ejercicios**: Ruta `/cliente/ejercicios` que permite consultar y ver técnicas de ejercicios con permisos de solo lectura (sin ABM).
- **ABM Avanzado de Clientes (Panel Admin)**:
  - Gestión completa de clientes en `/admin/usuarios` (alias `/admin/clientes`).
  - Alta en mostrador con activación instantánea de cuenta por defecto (`confirmado = 1`).
  - Buscador reactivo y chips de filtro (Todos, Confirmados, Pendientes, Con Membresía).
  - Ficha de perfil individual con cálculo de presentismo, membresía activa, historial de turnos y rutinas asignadas.
  - Modificación y baja segura con confirmación interactiva.
- **Panel de Entrenador (Tarjetas, Iconos y Módulo Alumnos)**:
  - Tarjetas reordenadas: 1° Clases y Control de Asistencia, 2° Alumnos, 3° Rutinas y WODs, 4° Banco de Ejercicios.
  - Iconos temáticos unificados (`icono--verde`, `icono--azul`, `icono--naranja`).
  - Módulo de "Alumnos" del profesor (`/entrenador/alumnos` y `/entrenador/alumnos/detalle`), con KPIs del profesor, historial de clases con ese entrenador y acceso a diseñar rutinas personalizadas con preselección automática del alumno.
- **Scrollable Sidebar**: Barra lateral de navegación con scrollbar personalizada para resoluciones reducidas o paneles con menús extensos.
- **Estilos 100% SCSS**: Refactorización de estilos inline hacia archivos `.scss` modulares (`_usuarios.scss`, `_agenda.scss`, `_ejercicios.scss`, `_configuracion.scss`).
- **Configuración del Sitio (White-Label)**: Módulo de personalización total del establecimiento en `/admin/configuracion` (nombre de gym en `<title>`, carga de logo y portada WebP, email de contacto dinámico para envíos de `Email.php`, WhatsApp con enlace directo y redes sociales en landing y footer).
- **Módulo 'Mi Cuenta' (`/cuenta`)**: Gestión de datos de perfil (nombre, apellido, teléfono, email) y cambio seguro de contraseña para todos los roles (Admin, Entrenador y Cliente) con acceso directo desde el perfil en la navegación.
- **Rediseño Centrado de Vistas de Auth**: Todas las vistas de autenticación (`/login`, `/crear-cuenta`, `/olvide`, `/recuperar`, `/mensaje`, `/confirmar-cuenta`) estructuradas con contenedor de tarjeta central (`.auth-card`), responsive para todos los dispositivos, encabezado con logo del establecimiento y botón "Volver al inicio" con flecha interactiva.
- **Navegación Desplegable en Paneles**: Sidebar colapsable (modo reducido con iconos / modo extendido) para los 3 paneles (admin, entrenador y cliente).
- **Adaptabilidad Multi-Gimnasio (White-Label y Modularidad)**: Soporte completo para comercializar a cualquier gimnasio (musculación pura, box de crossfit o integral). Selector explícito de disciplina en Planes (`musculacion` vs `crossfit`) con badges en el listado y selección automática de constructor de rutina. Interruptores en Configuración del Sitio (`habilitar_turnos` y `habilitar_crossfit`) para activar/ocultar reservas y adaptar los títulos de menús y paneles dinámicamente sin tocar código.
- **Detalles UI y Mobile-First en Rutinas y Planes**: Grilla de creación de rutinas/WODs responsive (`.rutinas-form-grid`) apilando inputs verticalmente en celulares (`< 768px`) y 2 columnas en desktop. Botón de nuevo plan con icono `+` y acciones de tabla con botones compactos e iconos intuitivos (`.btn-micro--editar` y `.btn-micro--eliminar`).
- **Asignación Multi-Alumno en Rutinas**: Capacidad de seleccionar múltiples alumnos simultáneamente mediante chips interactivos y buscador dinámico con prevención de duplicados. El backend clona de forma independiente la rutina para cada alumno seleccionado (Alternativa A).
- **Funcionalidad Mostrar / Ocultar Contraseña (Password Toggle)**: Módulo global en `src/js/app.js` (`iniciarPasswordToggle()`) que detecta y envuelve automáticamente todos los campos `input[type="password"]` del sistema (Login, Registro, Recuperación de contraseña, Mi Cuenta, Crear/Editar Usuario y Crear/Editar Entrenador) agregando un botón interactivo con icono de ojo (`fa-solid fa-eye` / `fa-solid fa-eye-slash`) con accesibilidad, foco y estilos adaptados a temas claros y oscuros.

---

## 8. Remediación de Seguridad (Auditoría Post-Producción)

En respuesta a la auditoría de seguridad para operar con pagos reales en producción, se implementaron las siguientes mejoras críticas:

1. **Credenciales y Secretos (Prioridad 1)**:
   - Eliminación del archivo `gym-deploy.zip` del working tree y purgado definitivo de todos los commits históricos del repositorio Git mediante `git-filter-repo`.
   - Inclusión de `*.zip` y `.env.*` en `.gitignore`. Verificación de `.env.example` con valores placeholder exclusivamente.
2. **Inyección SQL (Prioridad 2)**:
   - Sanitización de columnas con whitelist alfanumérica y escapado de valores con `self::$db->escape_string()` en `ActiveRecord::where()` y `ActiveRecord::whereAll()`.
   - Escapado estricto de variables en consultas directas: `Usuario::existeUsuario()`, `Reserva::cuposDisponibles()` y `Reserva::existeReservaUsuario()`.
3. **Tokens Criptográficos (Prioridad 3)**:
   - Reemplazo de `uniqid()` por `bin2hex(random_bytes(32))` (64 caracteres con 256 bits de entropía CSPRNG) en `Usuario::crearToken()` para confirmación y recuperación de contraseñas.
   - Actualización del esquema de base de datos a `token VARCHAR(64)` en los archivos SQL.
4. **Protección CSRF (Prioridad 4)**:
   - Pospuesta a pedido del desarrollador para una etapa posterior.
5. **Endurecimiento de Sesión (Prioridad 5)**:
   - Regeneración de ID de sesión (`session_regenerate_id(true)`) tras login exitoso en `LoginController::login()` contra ataques de *Session Fixation*.
   - Configuración de directivas de cookie en `Router.php` previo a `session_start()`: `httponly = true`, `samesite = 'Lax'` (garantizando compatibilidad con los retornos de Mercado Pago Checkout) y `secure` dinámico en entornos HTTPS.
   - Limpieza completa de sesión en `LoginController::logout()`: vaciado de `$_SESSION`, invalidación y borrado de la cookie de sesión del navegador y ejecución de `session_destroy()`.

### Acciones Pendientes del Desarrollador
1. **Base de Datos (InfinityFree / phpMyAdmin)**:
   Ejecutar la siguiente sentencia SQL:
   ```sql
   ALTER TABLE usuarios MODIFY COLUMN token VARCHAR(64) DEFAULT NULL;
   ```
2. **Subida de Archivos a Producción (`filemanager.ai`)**:
   - `models/ActiveRecord.php`
   - `models/Usuario.php`
   - `models/Reserva.php`
   - `Router.php`
   - `controllers/LoginController.php`

---

## 9. Módulo de Pagos y Facturación (Panel Admin)

Módulo integral de control financiero en `/admin/pagos` que proporciona visibilidad completa sobre ingresos, cobranzas y proyecciones de vencimientos:

- **Ajuste de Esquema (`pagos`)**:
  - `metodo_pago ENUM('mercadopago', 'efectivo') NOT NULL DEFAULT 'mercadopago'`
  - `fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP`
  - Backfill histórico ejecutado en local y producción sincronizando `fecha_pago` con `membresias.fecha_inicio`.
- **Registro Automático de Cobros**:
  - `PagoController::webhook()` y `PagoController::procesarExito()`: asignan `metodo_pago = 'mercadopago'` y `fecha_pago = NOW()`.
  - `UsuarioController::membresiaManual()`: asigna `metodo_pago = 'efectivo'` y `fecha_pago = NOW()`.
- **Modelo y Analítica (`models/Pago.php`)**:
  - Filtros de período flexibles (`mes_actual`, `mes_anterior`, `ultimos_30`, `anio_actual`, `historico`).
  - `obtenerKpisPorPeriodo()`: Total facturado, total aprobados con ticket promedio, pagos pendientes, pagos rechazados y desglose comparativo Mercado Pago vs Efectivo con porcentajes relativos.
  - `obtenerRecaudacionPorPlan()`: Total facturado y cantidad de transacciones agrupadas por plan/disciplina.
  - `obtenerProximosVencimientos()`: Detección preventiva de membresías a vencer en los próximos 7 días con DNI, teléfono y plan del alumno.
  - `obtenerPagosPorPeriodo()`: Listado transaccional enriquecido con datos del alumno (nombre, apellido, DNI, teléfono), plan y estado.
- **Controlador y Ruta**:
  - Ruta `GET /admin/pagos` despachada por `AdminController::pagos()` con verificación estricta de rol administrador (`isAdmin()`).
- **Vista y Experiencia de Usuario (`views/admin/pagos/index.php`)**:
  - Siguiendo el lenguaje visual de `views/admin/reportes.php`: tarjetas KPI con bordes semánticos, selectores de período por tabs y badge de fecha activa.
  - Comparativa de métodos de pago con barras de progreso estilizadas y desglose monetario.
  - Tabla de alertas de vencimientos próximos (<= 7 días) con botón de contacto directo por WhatsApp con mensaje prearmado y acceso a renovación manual en mostrador.
  - Tabla de transacciones con DNI, método con badge (`Mercado Pago` vs `Efectivo`), estado coloreado y referencia de comprobante.
  - Botón optimizado para imprimir reporte en limpio (`window.print()`).
- **Navegación**:
  - Tarjeta en dashboard principal (`views/admin/index.php`) ubicada inmediatamente después de "Gestión de Clientes".
  - Enlace directo en el sidebar de administración (`views/layout.php`) ubicado inmediatamente después de "Gestión de Clientes".

---

## 10. Cómo me gusta trabajar

- **Paso a paso**: Avance modular en cambios pequeños y concretos, evitando bloques gigantescos o etapas completas de una sola vez.
- **Resumen tras cada paso**: Indicar con claridad qué archivos se crearon o modificaron y dar instrucciones precisas de cómo probarlo en el navegador.
- **Confirmación explícita**: Esperar el visto bueno del desarrollador antes de continuar con el siguiente paso o etapa.
- **Alineación previa**: Ante cualquier duda arquitectónica, de convención o regla de negocio, consultar siempre antes de asumir.

