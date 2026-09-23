# RadioAlgoMas

Plataforma interna del portal de noticias **RadioAlgoMas**: blog/actualidad, reproducción de video en vivo, ingesta automática de contenido vía RSS y panel de administración.

> Repositorio privado. El contenido y las credenciales de producción no deben exponerse fuera del equipo autorizado.

## Tabla de contenidos

- [Descripción general](#descripción-general)
- [Arquitectura](#arquitectura)
- [Stack tecnológico](#stack-tecnológico)
- [Requisitos](#requisitos)
- [Puesta en marcha (entorno local)](#puesta-en-marcha-entorno-local)
- [Variables de entorno](#variables-de-entorno)
- [Ejecución y servicios](#ejecución-y-servicios)
- [Panel de administración](#panel-de-administración)
- [Rutas públicas](#rutas-públicas)
- [Tareas programadas](#tareas-programadas)
- [Comandos Artisan](#comandos-artisan)
- [Calidad de código y pruebas](#calidad-de-código-y-pruebas)
- [Despliegue](#despliegue)
- [Convenciones de trabajo](#convenciones-de-trabajo)
- [Propiedad y contacto](#propiedad-y-contacto)

## Descripción general

RadioAlgoMas es un portal de noticias con las siguientes capacidades:

- **Publicación de contenido**: artículos con categorías, etiquetas, artículos destacados (permanentes o por tiempo limitado), búsqueda y paginación.
- **Streaming en vivo**: reproductor basado en Video.js/HLS con un proxy propio que evita problemas de CORS y expone el stream activo.
- **Automatización de contenido**: importación de fuentes RSS, detección de duplicados, reescritura de texto y publicación programada mediante colas.
- **SEO y distribución**: sitemap XML, feed RSS, metadatos Open Graph/Twitter, schema markup y notificación automática a la Google Indexing API.
- **Publicidad**: gestión de anuncios con registro de clics.
- **Administración**: panel Filament con roles y permisos (Filament Shield).

## Arquitectura

Flujo principal de contenido:

```
Fuentes RSS ──► rss:import ──► Cola (ProcessRssContentJob)
                                    │
                                    ├─ DuplicateDetectorService  (descarta duplicados)
                                    ├─ ContentRewriterService     (reescribe contenido)
                                    └─ ScheduleArticlePublishJob  (programa publicación)
                                                    │
                                                    ▼
                                          Artículo publicado ──► ArticleObserver ──► Google Indexing API
```

Componentes destacados:

- `StreamProxyController` / `SimpleStreamProxyController`: proxy del stream en vivo.
- `PlayerUrlObserver` + comandos `stream:*`: activación, monitoreo y testeo de URLs de streaming.
- `bootstrap/app.php`: definición del scheduler y del routing de la aplicación.

## Stack tecnológico

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12 |
| Administración | Filament 3.3, Livewire 3 / Volt, Flux UI |
| Frontend | Tailwind CSS 4, Vite 7, Video.js 8 |
| Base de datos | MySQL / MariaDB (por defecto) o SQLite |
| Colas / caché / sesiones | Driver `database` |
| Medios | Intervention Image, willvincent/feeds |
| Integraciones | Google API Client (Indexing API) |
| Calidad | Pest 3, Laravel Pint |

## Requisitos

- PHP >= 8.2 con extensiones: `gd`, `intl`, `pdo`, `mbstring`, `openssl`, `curl`, `fileinfo`
- Composer 2
- Node.js 22+ y npm
- MySQL/MariaDB (recomendado) o SQLite
- Credenciales de Flux UI (paquete privado de Livewire) para `composer install`

## Puesta en marcha (entorno local)

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio> radioalgomas
cd radioalgomas

# 2. Configurar acceso al repositorio privado de Flux UI
composer config http-basic.composer.fluxui.dev "<usuario>" "<licencia>"

# 3. Instalar dependencias
composer install
npm install

# 4. Crear entorno y generar clave
cp .env.example .env
php artisan key:generate

# 5. Configurar la base de datos en .env y migrar
php artisan migrate

# 6. Sembrar datos base (ver nota)
php artisan db:seed --class=BlogSeeder
php artisan db:seed --class=ShieldSeeder
php artisan db:seed --class=PlayerSeeder

# 7. Enlace de almacenamiento y assets
php artisan storage:link
npm run build
```

> En Windows PowerShell usa `Copy-Item .env.example .env`.
>
> Nota: `DatabaseSeeder` solo invoca `BlogSeeder`. Los roles/permisos (`ShieldSeeder`) y los reproductores (`PlayerSeeder`) deben sembrarse explícitamente si el entorno los necesita.

## Variables de entorno

Configura estas variables en `.env` (los valores reales se gestionan fuera del repositorio):

| Grupo | Variables |
| --- | --- |
| Aplicación | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `APP_LOCALE` |
| Base de datos | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` |
| Sesión / colas / caché | `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE` |
| Correo | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` |
| Streaming | `STREAM_TEST_TIMEOUT`, `STREAM_CONNECTION_TIMEOUT`, `STREAM_RETEST_INTERVAL`, `STREAM_HEALTH_CHECK_INTERVAL`, `STREAM_MAX_FAILED_ATTEMPTS` |
| Google Indexing | `GOOGLE_INDEXING_SERVICE_ACCOUNT_PATH` (ruta al JSON de la cuenta de servicio) |

> Nunca subas el archivo `.env` ni el JSON de la cuenta de servicio al repositorio.

## Ejecución y servicios

```bash
# Servidor, worker de colas y Vite en paralelo
composer run dev

# O por separado
php artisan serve         # servidor HTTP
php artisan queue:listen  # procesamiento de colas
npm run dev               # Vite en modo watch
php artisan schedule:work # planificador (tareas programadas)
```

## Panel de administración

- URL local: `/tv-panel`
- Recursos: Artículos, Categorías, Etiquetas, Fuentes RSS, Players/URLs, Anuncios y Usuarios.
- Roles: `super_admin`, `admin`, `editor`, `author`, `subscriber` (definidos en `ShieldSeeder`).

El usuario administrador inicial lo define el seeder de contenido; no se documentan credenciales en este README. Consulta el código del seeder o el gestor de secretos del equipo.

## Rutas públicas

| Método | Ruta | Descripción |
| --- | --- | --- |
| GET | `/` y `/blog` | Home del portal |
| GET | `/blog/{slug}` | Detalle de artículo |
| GET | `/blog/categoria/{slug}` | Artículos por categoría |
| GET | `/blog/etiqueta/{slug}` | Artículos por etiqueta |
| GET | `/rss` | Feed RSS |
| GET | `/sitemap.xml` | Sitemap |
| GET | `/stream-proxy/active` | Proxy del stream activo |
| GET | `/ads/{ad}/click` | Registro de clic de anuncio |

## Tareas programadas

Definidas en `bootstrap/app.php` (requieren `php artisan schedule:work` o el cron del servidor):

| Tarea | Frecuencia | Comando |
| --- | --- | --- |
| Importación rotativa de fuentes | Cada minuto | `rss:import --rotate` |
| Importación completa de respaldo | Cada 2 horas | `rss:import --force` |
| Procesamiento de contenido importado | Cada 30 minutos | `rss:process-with-delay` |
| Reescritura de contenido pendiente | Cada 4 horas | `news:rewrite-content` |

## Comandos Artisan

```bash
# Contenido / RSS
php artisan rss:import                 # Importar noticias desde fuentes RSS
php artisan rss:process-with-delay     # Procesar contenido importado con retardo
php artisan news:rewrite-content       # Reescribir contenido (anti-duplicados)
php artisan tags:clean                 # Limpiar etiquetas huérfanas

# Streaming
php artisan stream:activate            # Activar un stream
php artisan stream:status              # Estado actual del stream
php artisan stream:monitor             # Monitorear disponibilidad
php artisan stream:test {url?}         # Probar un stream
php artisan stream:test-url {url}      # Probar una URL específica
php artisan stream:test-all            # Probar todos los streams
php artisan stream:create-test-url     # Crear una URL de prueba
php artisan players:list               # Listar reproductores
```

## Calidad de código y pruebas

```bash
# Formato (Laravel Pint)
vendor/bin/pint

# Pruebas (Pest)
composer test
# o
./vendor/bin/pest
```

Integración continua (GitHub Actions):

- `.github/workflows/tests.yml`: instala dependencias, compila assets y ejecuta Pest.
- `.github/workflows/lint.yml`: ejecuta Pint sobre `develop` y `main`.

## Despliegue

- El proyecto incluye imágenes Docker para PHP 8.0–8.4 (`docker/<version>`) compatibles con Laravel Sail, con `supervisord` para procesos en contenedor.
- En producción se requiere: worker de colas (`queue:work`) y planificador activos, `APP_DEBUG=false`, caché de configuración/rutas (`config:cache`, `route:cache`) y assets compilados (`npm run build`).
- Las credenciales de Google Indexing se montan como archivo/secretos y no forman parte del repositorio.

Pasos sugeridos de release:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
npm ci && npm run build
php artisan queue:restart
```

## Convenciones de trabajo

- Ramas principales: `main` (producción) y `develop` (integración). El CI se ejecuta sobre ambas.
- Crea ramas de trabajo desde `develop` con nombres descriptivos (por ejemplo `feature/…`, `fix/…`).
- Antes de abrir un PR: ejecuta `vendor/bin/pint` y `composer test`, y describe el cambio y su impacto.
- No incluir secretos, `.env` ni dumps de base de datos en commits.

## Propiedad y contacto

- Equipo responsable: RadioAlgoMas
- Canal de soporte / incidencias: RadioAlgoMas
- Documentación interna adicional: RadioAlgoMas

Repositorio privado de uso interno. Todos los derechos reservados.
