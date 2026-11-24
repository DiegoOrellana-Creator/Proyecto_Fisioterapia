# FisioAgenda SCZ

Prototipo de plataforma web para la gestión de horarios y reservas de servicios de fisioterapia en Santa Cruz de la Sierra.
Proyecto de **Metodología de la Investigación**, UAGRM (FICCT), 2026.

Usa el mismo stack que el proyecto *2do_Clinica*: **Laravel 12 (PHP 8.2)**, **Blade + Tailwind CSS + Alpine.js** (Laravel Breeze), **Vite** y **PostgreSQL**.

## Módulos (sección 9.6 del documento)

| Módulo | Qué incluye |
|---|---|
| Usuarios | Registro de pacientes, ingreso, recuperación de contraseña, perfil. Roles: paciente, profesional, administrador (general o de un establecimiento). Activar o desactivar cuentas. |
| Establecimientos | Búsqueda por nombre, zona y especialidad. Ficha pública con dirección, contacto, WhatsApp, servicios, profesionales y opiniones. |
| Profesionales | Perfil público (especialidad, matrícula, experiencia, horario, opiniones). Alta de profesionales con su cuenta de acceso. |
| Servicios | Catálogo con precio, duración, categoría y sesiones sugeridas; comparación entre establecimientos. |
| Horarios y disponibilidad | Horario semanal por profesional (admite horario partido) y bloqueos por vacaciones o ausencias. Los horarios libres se calculan descontando citas y bloqueos. |
| Reservas y citas | Reserva de una o varias sesiones semanales, confirmación por el establecimiento, cancelación y reprogramación, registro de asistencia o inasistencia, historial de cambios de estado, valoración de 1 a 5 estrellas. |
| Panel de administración | Resumen de los últimos 30 días (tasa de inasistencia, citas por estado, ingresos estimados), asistencia por profesional, agenda semanal y listados con filtros. |

Estados de una cita: `solicitada → confirmada → atendida | no_asistio`, con `reprogramada` y `cancelada` como desvíos.
Las reservas se registran dentro de una transacción que bloquea la fila del profesional (`lockForUpdate`), así dos pacientes no pueden quedarse con el mismo horario.

## Instalación con PostgreSQL

1. Crea la base de datos en PostgreSQL (por ejemplo con pgAdmin): `fisioagenda`.
2. Copia la configuración y completa tu contraseña de PostgreSQL:
   ```
   copy .env.example .env
   ```
   En `.env`: `DB_CONNECTION=pgsql`, `DB_DATABASE=fisioagenda`, `DB_USERNAME=postgres`, `DB_PASSWORD=tu_contraseña`.
3. Instala y prepara:
   ```
   composer install
   php artisan key:generate
   php artisan migrate:fresh --seed
   npm install
   npm run build
   ```
4. Levanta el servidor: `php artisan serve` y abre http://127.0.0.1:8000

> En esta copia, `vendor/` y `node_modules/` ya vienen incluidos y `.env` está configurado con **SQLite** (`database/database.sqlite`) para que funcione sin configurar nada. Para pasar a PostgreSQL basta con cambiar las variables `DB_*` del `.env` y volver a ejecutar `php artisan migrate:fresh --seed`.

## Despliegue en Render + Neon (gratis)

La página corre en **Render** (con el `Dockerfile`) y la base de datos PostgreSQL en **Neon**.

1. **Neon** ([neon.com](https://neon.com)): crea un proyecto en la región *AWS US East 2 (Ohio)*. En *Connect*, desactiva *Connection pooling* y copia la cadena de conexión (`postgresql://usuario:clave@ep-....neon.tech/neondb?sslmode=require`).
2. **GitHub**: sube este proyecto a un repositorio.
3. **Render** ([render.com](https://render.com)): *New → Blueprint*, elige el repositorio. Render lee `render.yaml` y te pide tres valores:
   - `APP_KEY`: genérala en tu PC con `php artisan key:generate --show` y pega el resultado completo (empieza con `base64:`).
   - `DB_URL`: la cadena de conexión de Neon.
   - `DEMO_PASSWORD`: la contraseña que tendrán las cuentas de demostración en el sitio público.
4. Espera a que termine el despliegue (unos 5–10 minutos la primera vez). En cada arranque el contenedor ejecuta las migraciones y, solo si la base está vacía, carga los datos de demostración.

Notas del plan gratuito: el servicio se suspende tras unos 15 minutos sin visitas y la primera carga después tarda 30–60 segundos. Los correos (por ejemplo, recuperar contraseña) no se envían: quedan en el registro (`MAIL_MAILER=log`).

## Cuentas de demostración

En tu PC todas usan la contraseña `password`. En el sitio publicado usan la que definas en `DEMO_PASSWORD`.

| Rol | Correo |
|---|---|
| Administrador general | `admin@fisioagenda.test` |
| Administrador del Centro Kinésico Equipetrol | `admin.equipetrol@fisioagenda.test` |
| Fisioterapeuta | `lucia.rojas@fisioagenda.test` (y los demás `nombre.apellido@fisioagenda.test`) |
| Paciente con historial | `paciente@fisioagenda.test` |

Los establecimientos, profesionales y pacientes son ficticios. Las fechas de las citas de ejemplo se generan en relación con el día en que se ejecuta el seeder.

## Sistema de diseño

La guía de estilo está en `/guia-de-estilo` ([resources/views/sitio/guia.blade.php](resources/views/sitio/guia.blade.php)). Es la "ficha de mostrador" de la skill `frontend-design`: colores con su contraste medido (WCAG 2.1 AA), tipografía (Instrument Sans y Spline Sans Mono para precios, horas y códigos), espaciado, radios y foco, botones, campos y estados.

## Pruebas

```
php artisan test
```

`tests/Feature/ReservaTest.php` comprueba las reglas de negocio: horarios libres según duración, citas duplicadas, bloqueos, reservas de varias sesiones (todo o nada), cancelación, reprogramación, registro de asistencia, permisos por rol y valoraciones.

## Estructura principal

```
app/Services/Disponibilidad.php    Cálculo de horarios libres
app/Services/Reservas.php          Registro y reprogramación de reservas (transaccional)
app/Http/Controllers/SitioController.php           Páginas públicas
app/Http/Controllers/ReservaController.php         Flujo de reserva y disponibilidad (JSON)
app/Http/Controllers/Paciente/CitaController.php   Mis citas, cancelar, reprogramar, valorar
app/Http/Controllers/Gestion/*                     Agenda, asistencia, horarios (profesional y admin)
app/Http/Controllers/Admin/*                       Resumen, establecimientos, servicios, profesionales, usuarios
database/migrations/2026_11_22_000100_create_fisioterapia_tablas.php   Modelo relacional
```
