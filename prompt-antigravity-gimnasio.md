# Contexto del proyecto

Estoy desarrollando un **sistema de gestión de gimnasio** en PHP con arquitectura **MVC propia** (sin framework), siguiendo exactamente el mismo estilo y convenciones que uso en mis otros proyectos (AppSalon, Ecommerce, BienesRaices — todos en mi GitHub, usuario **Brancoo17**).

Ya usé Cursor (en modo Plan) para analizar mis repos, definir la arquitectura completa, el modelo de datos y un plan de desarrollo en 8 etapas. Cursor empezó a programar la **Etapa 1 (Scaffold)** pero avanzó muy poco y se quedó a mitad de camino, sin completarla.

Tu tarea es **retomar el desarrollo desde donde quedó**, respetando al pie de la letra la arquitectura, las convenciones y el plan ya definidos (que te pego íntegro más abajo). No propongas una arquitectura alternativa ni introduzcas frameworks o librerías que no estén ya justificadas en el plan.

**Importante: quiero continuar con lo que ya está hecho, no reescribirlo ni empezar de cero.** Lo que Cursor ya generó en la Etapa 1 es la base sobre la que seguimos; la idea es completar y corregir lo que falte, no descartarlo.

**La base de datos ya está creada.** Ya ejecuté el script `gym_mvc.sql` con todas las tablas del modelo de datos (usuarios, planes, membresias, pagos, entrenador_clientes, horarios, reservas, asistencias, ejercicios, rutinas, rutina_ejercicios, avisos_enviados). No hace falta crearlas ni migrarlas: dalas por existentes en MySQL y verificá el archivo `sql/gym_mvc.sql` para confirmar los nombres de columnas exactos antes de escribir cualquier query o modelo.

---

## Sobre esta iteración puntual

**Este primer mensaje es solo para que tengas todo el contexto del proyecto — no quiero que generes ni modifiques código todavía.** Quiero avanzar de a poco: que primero audites y me cuentes el estado real del repo (ver siguiente sección), y recién en los próximos mensajes iremos paso a paso, tarea por tarea. Después de cada cambio que hagas quiero probarlo yo mismo para verificar que esté andando bien antes de seguir con lo siguiente. Así que no avances varios pasos de una: hacé uno, contámelo, y esperá que yo lo pruebe y te confirme antes de continuar.

---

## Antes de tocar código

1. **Inspeccioná el estado actual del repo** (carpetas, archivos ya creados, `composer.json`, `package.json`, `gulpfile.js`, SQL, etc.) para entender exactamente qué hizo Cursor y qué falta.
2. Compará ese estado real contra la Etapa 1 del plan (Scaffold: Router, ActiveRecord, Gulp/SASS, layout, SQL, seed admin + 3 planes) y decime un resumen breve de:
   - Qué está completo.
   - Qué está a medias o mal encaminado.
   - Qué falta directamente.
3. Recién ahí, proponé cómo continuar (completar la Etapa 1 y seguir con las siguientes) y esperá mi confirmación antes de generar o modificar código, igual que veníamos trabajando con Cursor.

---

## Convenciones tomadas de mis repos (no negociables)

- El MVC vive en la **raíz del proyecto**, no dentro de `app/` (esto reemplaza cualquier mención genérica a `app/controllers` de prompts anteriores).
- `Router.php` + `public/index.php` como front controller y registro de rutas GET/POST.
- Autoload **PSR-4** con namespaces: `MVC\`, `Controllers\`, `Model\`, `Classes\`.
- **ActiveRecord propio** (sin ORM externo): métodos `all`, `find`, `where`, `SQL`, `guardar`, `sincronizar`, `alertas`.
- `includes/app.php`, `includes/database.php`, `includes/funciones.php` — con helpers como `s()`, `debuguear()`, `isAuth()`, `isAdmin()`, y sumar `isEntrenador()`.
- Vistas en `views/layout.php` + carpetas por área, renderizadas con `$router->render('area/vista', [...])`.
- **SASS + Gulp** (`src/scss`, `src/js` → `public/build`), JavaScript vanilla con `fetch` para las APIs.
- **Composer solo para librerías puntuales** (no para el MVC en sí): `phpmailer/phpmailer`, `intervention/image`, `mercadopago/dx-php`.
- Validaciones de precio, stock/cupo y todo lo sensible: **siempre en el servidor**, nunca confiando en el cliente.
- Nada de Laravel, nada de frameworks de frontend, nada de FullCalendar (calendario propio y liviano, estilo AppSalon) salvo que yo lo pida explícitamente.

---

## Plan completo definido con Cursor (usarlo como fuente de verdad)

### Estructura de carpetas

```
Router.php
composer.json / gulpfile.js / package.json
controllers/   Login, Paginas, Admin, Cliente, Entrenador, Plan, Membresia, Pago, Horario, Reserva, Ejercicio, Rutina, Asistencia, API, Reporte
models/        ActiveRecord, Usuario, Plan, Membresia, Pago, Horario, Reserva, Ejercicio, Rutina, RutinaEjercicio, EntrenadorCliente, AvisoEnviado
classes/       Email.php, MercadoPagoService.php
includes/      app.php, database.php, funciones.php
views/         layout.php, auth/, paginas/, cliente/, admin/, entrenador/, templates/
public/        index.php, .htaccess, build/, imagenes/
src/           scss/, js/, img/
scripts/       avisos-vencimiento.php   (cron diario)
sql/           gym_mvc.sql
```

### Modelo de datos

Regla de negocio confirmada: un usuario puede tener **varias membresías activas en paralelo**, pero a lo sumo **una activa por par usuario+plan** (se valida en servidor, igual que el stock en el ecommerce).

Relaciones:
- `usuarios` 1—N `membresias`, `planes` 1—N `membresias`, `membresias` 1—N `pagos`
- `usuarios` (entrenador) 1—N `entrenador_clientes` N—1 `usuarios` (cliente)
- `planes` 1—N `horarios` N—1 `usuarios` (entrenador dicta)
- `horarios` 1—N `reservas` N—1 `usuarios`
- `reservas` 1—0/1 `asistencias`
- `usuarios` (entrenador) 1—N `rutinas` → `rutina_ejercicios` N—1 `ejercicios`

Tablas principales:
- **usuarios**: nombre, apellido, email, password, telefono, rol (cliente|admin|entrenador), confirmado, token
- **planes**: nombre, descripcion, precio, duracion_dias, cupo_opcional, imagen, activo (seed: Crossfit, Musculación, Funcional)
- **membresias**: usuario_id, plan_id, estado (pendiente|activa|vencida|cancelada), fecha_inicio, fecha_fin
- **pagos**: membresia_id, monto, estado, mp_preference_id, mp_payment_id, mp_status
- **entrenador_clientes**: entrenador_id, cliente_id (alta desde admin)
- **horarios**: plan_id, entrenador_id, dia_semana (0-6), hora_inicio, hora_fin, cupo
- **reservas**: usuario_id, horario_id, fecha, estado (reservada|cancelada) — unique (usuario, horario, fecha)
- **asistencias**: reserva_id, presente, marcado_por
- **ejercicios**: nombre, grupo_muscular, descripcion, imagen, video_url
- **rutinas / rutina_ejercicios**: asignada a un cliente por un entrenador (series, reps, dia, orden)
- **avisos_enviados**: membresia_id, tipo (7dias|hoy), enviado_at — evita mails duplicados

### Roles y flujos

Login único (mismo patrón que AppSalon), redirección por rol:
- admin → `/admin`
- entrenador → `/entrenador`
- cliente → `/cliente`

Registro público solo para clientes (con email de confirmación). El admin da de alta entrenadores/admins ya confirmados.

- **Cliente:** ver sus membresías (puede tener varias), pagar/renovar, historial de pagos, reservar turno (si tiene membresía activa de ese plan y hay cupo), ver su rutina, ver su asistencia.
- **Admin:** ABM de usuarios/planes/horarios/ejercicios, asignar alumnos a entrenadores, ver pagos, agenda general, reportes, marcar asistencia.
- **Entrenador:** ver alumnos asignados, armar/asignar rutinas, ver horarios de sus clases, marcar asistencia de su clase.

Reserva de turno: `cupo_disponible = horario.cupo - COUNT(reservas reservadas ese día)`. Precio, estado de membresía y cupo siempre se calculan y validan en PHP (server-side).

### Mercado Pago

1. Cliente elige un plan → se crea una membresía en estado `pendiente` con `monto = planes.precio`.
2. El backend crea una `Preference` (Checkout Pro) con `external_reference` = id de la membresía.
3. Webhook `POST /pagos/webhook`: verifica el pago y activa la membresía (`fecha_inicio` = hoy, `fecha_fin` = hoy + `duracion_dias`).
4. Credenciales en `includes/` (nunca commitear tokens). Arrancar en modo sandbox.

### Funcionalidades extra confirmadas

- **Asistencia:** el entrenador o admin marca presentismo sobre la reserva del día; el cliente puede ver su historial de asistencia.
- **Avisos de vencimiento:** `scripts/avisos-vencimiento.php` (cron diario) + PHPMailer; se envían a los 7 días antes y el día del vencimiento; se registra en `avisos_enviados` para evitar duplicados. Un job aparte puede pasar membresías de `activa` a `vencida`.
- **Reportes para admin:** membresías activas/vencidas, ingresos del mes (pagos aprobados), ocupación de clases, alumnos por entrenador.

### Etapas de desarrollo (cada una debe quedar usable y revisable antes de avanzar a la siguiente)

1. **Scaffold** — Router, ActiveRecord, Gulp/SASS, layout, SQL, seed admin + 3 planes. *(Etapa en la que Cursor se quedó a medias — arrancar por acá.)*
2. **Auth + landing** — login/registro/recuperar contraseña/confirmar cuenta; landing pública (planes, horarios, ubicación, contacto); paneles vacíos por rol.
3. **Planes** — CRUD admin + Intervention Image (WebP); listado público de planes.
4. **Membresías + Mercado Pago** — checkout, webhook, historial de pagos, varias membresías activas (una por plan).
5. **Horarios y turnos** — ABM de horarios, API con fetch (estilo AppSalon), control de cupo, calendario para admin/entrenador.
6. **Ejercicios y rutinas** — ABM de ejercicios; el entrenador arma y asigna rutinas a sus clientes.
7. **Asistencia, avisos y reportes** — presentismo, cron de emails/membresías vencidas, dashboard de reportes para admin.
8. **Calendar JS** — calendario propio liviano (no FullCalendar), integrado a horarios/turnos.

### Fuera de alcance (salvo que yo lo pida explícitamente más adelante)

App nativa, integración con WhatsApp, facturación AFIP, renovación automática vía Mercado Pago, chat interno.

---

## Cómo quiero que trabajes de acá en adelante

1. Auditá el estado real del repo antes de escribir una sola línea (ver sección "Antes de tocar código"). En este primer mensaje quiero solo ese diagnóstico, todavía no código.
2. Retomá exactamente donde quedó Cursor, sobre lo ya generado, completando primero la Etapa 1 y siguiendo el orden de las etapas tal cual está definido arriba.
3. No renombres carpetas, no cambies convenciones de nombres, no introduzcas patrones nuevos sin avisarme y justificarlo.
4. Trabajemos **paso a paso, en pasos chicos**: un cambio o una funcionalidad concreta por vez, no varias etapas ni varios archivos grandes de un saque.
5. Después de cada paso, contame qué archivos creaste o modificaste y qué debería probar yo para verificar que funciona.
6. Esperá que yo pruebe y te confirme antes de seguir con el siguiente paso o de pasar a la próxima etapa. No avances por tu cuenta.
