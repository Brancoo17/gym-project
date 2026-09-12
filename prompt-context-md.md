Quiero que crees un archivo **`CONTEXT.md`** en la raíz del proyecto. Su objetivo es que cualquier agente de IA (vos mismo en una sesión futura, u otro) pueda leerlo al arrancar y tener de inmediato todo el contexto del proyecto, sin que yo tenga que volver a explicarlo desde cero.

No necesito que sea extenso ni grandilocuente: priorizá que sea claro, concreto y fácil de escanear (títulos, listas, tablas cortas). Actualizalo en base a todo lo que ya sabés de este proyecto hasta ahora (arquitectura, convenciones, modelo de datos, plan de etapas, estado real del repo) y a cómo veníamos trabajando. Estructuralo con estas secciones:

## 1. Qué es el proyecto

Descripción breve: sistema de gestión de gimnasio en PHP con arquitectura MVC propia (sin framework), mismo stack y convenciones que uso en mis otros proyectos (AppSalon, Ecommerce, BienesRaices — GitHub: Brancoo17).

## 2. Stack y arquitectura (no negociable)

- PHP MVC propio, sin framework, viviendo en la **raíz del proyecto** (no en `app/`).
- MySQL, con `sql/gym_mvc.sql` como fuente de verdad del modelo de datos (las tablas **ya están creadas**, no hay que migrarlas).
- SASS + Gulp para estilos, JavaScript vanilla con `fetch` para AJAX.
- `Router.php` + `public/index.php` como front controller.
- PSR-4: namespaces `MVC\`, `Controllers\`, `Model\`, `Classes\`.
- ActiveRecord propio (`all`, `find`, `where`, `SQL`, `guardar`, `sincronizar`, `alertas`) — sin ORM externo.
- `includes/app.php`, `database.php`, `funciones.php` con helpers `s()`, `debuguear()`, `isAuth()`, `isAdmin()`, `isEntrenador()`.
- Vistas en `views/`, renderizadas con `$router->render('area/vista', [...])`.
- Composer solo para librerías puntuales: `phpmailer/phpmailer`, `intervention/image`, `mercadopago/dx-php`.
- Validaciones sensibles (precio, cupo, estado de membresía) siempre server-side.

## 3. Qué NO hacer

- No introducir frameworks (Laravel, Symfony, etc.) ni frameworks de frontend.
- No renombrar carpetas ni cambiar las convenciones ya establecidas sin avisar y justificarlo.
- No reescribir desde cero lo que ya está hecho: continuar sobre la base existente.
- No generar ni modificar código sin que yo lo haya pedido explícitamente para ese paso puntual.
- No avanzar de una etapa a la siguiente, ni encadenar varios pasos grandes, sin mi confirmación.

## 4. Modelo de datos (resumen)

Tabla corta con las entidades principales y su rol (usuarios, planes, membresias, pagos, entrenador_clientes, horarios, reservas, asistencias, ejercicios, rutinas, rutina_ejercicios, avisos_enviados), y la regla de negocio clave: un usuario puede tener varias membresías activas en paralelo, pero máximo una activa por par usuario+plan (validado en servidor).

## 5. Roles y flujos

Resumen de los tres roles (cliente, admin, entrenador) y qué puede hacer cada uno, más el flujo de pago con Mercado Pago (Checkout Pro + webhook).

## 6. Plan de etapas y estado actual

Listá las 10 etapas definidas marcando cuál está en curso y un resumen breve de qué hay hecho y qué falta dentro de la etapa actual (completá esto con el diagnóstico real que ya hiciste del repo).

## 7. Cómo me gusta trabajar

- Avanzamos paso a paso, en cambios chicos y concretos, no en bloques grandes ni etapas completas de una.
- Después de cada paso, quiero un resumen de qué archivos se crearon o modificaron y qué debo probar yo para verificar que funciona.
- Espero mi confirmación explícita antes de seguir con el próximo paso o pasar a la siguiente etapa.
- Ante cualquier duda de arquitectura o convención, preguntame antes de asumir o decidir por tu cuenta.

---

Una vez creado el archivo, no seas verbose respondiéndome: solo confirmame que `CONTEXT.md` quedó creado y dame un resumen de 3-4 líneas de su contenido. A partir de ahora, este archivo es la referencia que hay que mantener actualizada: cada vez que cerremos una etapa o cambiemos algo importante de arquitectura o de reglas de trabajo, avisame para actualizarlo.
