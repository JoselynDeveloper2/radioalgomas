# Búsqueda de marca "radioalgomas" — Plan de mejoras

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Que Google reconozca "radioalgomas" como marca (y deje de corregirlo a "radio gomas") y muestre radioalgomas.com como primer resultado en EE. UU. (Maryland).

**Architecture:** En el sitio: datos estructurados `WebSite` + `RadioStation` en la portada con todas las variantes del nombre (`alternateName`), título de portada con ambas grafías, canónica única para `/` y `/blog`, y `og:locale` de EE. UU. Fuera del sitio: Google Business Profile, perfiles sociales y directorios que apunten a `https://radioalgomas.com`, más seguimiento en Search Console.

**Tech Stack:** Laravel 12, Blade, Pest 3.

## Global Constraints

- Dominio canónico: `https://radioalgomas.com` (sin www; ya hay 301 para las demás variantes).
- Nombre oficial: `Radio Algo Más` (`config('radio.name')` / `RadioSetting::current()->station_name`). `APP_NAME=RadioAlgoMas`.
- Variantes que debe conocer Google: `RadioAlgoMas`, `Radio Algo Mas`, `radioalgomas`, `radioalgomas.com`.
- Sin dependencias nuevas.

## Diagnóstico (videos del cliente, 26-sep-2026, Beltsville MD)

- Búsqueda `radioalgomas` → Google muestra "Se muestran resultados de **radio gomas**. Buscar, en cambio, radioalgomas".
- El único resultado de la marca es radiosplay.com, que enlaza a `http://www.radioalgomas.com/`.
- Causa: Google no tiene suficientes señales de que "radioalgomas" es una marca.
- Hoy el sitio no tiene JSON-LD de marca: solo `NewsArticle` en las notas (`resources/views/components/schema-markup.blade.php`).

---

### Task 1: Datos estructurados de marca en la portada

**Files:**
- Modify: `config/radio.php` (agregar `alternate_names` y `same_as`)
- Create: `resources/views/components/site-schema.blade.php`
- Modify: `resources/views/blog/index.blade.php` (hacer push del schema)
- Test: `tests/Feature/SiteSchemaTest.php`

**Interfaces:**
- Produces: `config('radio.alternate_names')`: `string[]`, `config('radio.same_as')`: `string[]` (URLs de perfiles oficiales; la Task 4 los completa), componente `<x-site-schema />`.

- [ ] **Step 1: Escribir el test que falla**

`tests/Feature/SiteSchemaTest.php`:

```php
<?php

function siteLdJson(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    return array_map(fn ($json) => json_decode($json, true), $m[1]);
}

test('home publishes WebSite and RadioStation schema with brand name variants', function () {
    $graph = collect(siteLdJson($this->get('/')->getContent()))->pluck('@graph')->filter()->flatten(1);
    $website = $graph->firstWhere('@type', 'WebSite');
    $station = $graph->firstWhere('@type', 'RadioStation');

    expect($website['url'])->toBe(route('home'))
        ->and($website['name'])->toBe('Radio Algo Más')
        ->and($website['alternateName'])->toContain('RadioAlgoMas', 'radioalgomas')
        ->and($station['url'])->toBe(route('home'))
        ->and($station['alternateName'])->toContain('radioalgomas.com')
        ->and($station['logo'])->toBe(asset('images/logo.png'));
});
```

- [ ] **Step 2: Correrlo y verificar que falla**

Run: `php artisan test --filter=SiteSchemaTest`
Expected: FAIL (`$website` es null: todavía no hay schema en la portada).

- [ ] **Step 3: Agregar la configuración**

En `config/radio.php`, después de `'name' => 'Radio Algo Más',`:

```php
    // Variantes con las que la gente escribe la marca; van en el JSON-LD para que
    // Google deje de "corregir" radioalgomas a "radio gomas".
    'alternate_names' => ['RadioAlgoMas', 'Radio Algo Mas', 'radioalgomas', 'radioalgomas.com'],

    // URLs de los perfiles oficiales (Facebook, Instagram, YouTube, Google Business...).
    'same_as' => [],
```

- [ ] **Step 4: Crear el componente**

`resources/views/components/site-schema.blade.php`:

```blade
@php
    $name = \App\Models\RadioSetting::current()->station_name;
    $alternateNames = config('radio.alternate_names');

    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => route('home') . '#website',
                'url' => route('home'),
                'name' => $name,
                'alternateName' => $alternateNames,
                'inLanguage' => 'es',
            ],
            array_filter([
                '@type' => 'RadioStation',
                '@id' => route('home') . '#station',
                'url' => route('home'),
                'name' => $name,
                'alternateName' => $alternateNames,
                'logo' => asset('images/logo.png'),
                'sameAs' => config('radio.same_as') ?: null,
            ]),
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
```

- [ ] **Step 5: Insertarlo en la portada**

En `resources/views/blog/index.blade.php`, justo antes de `@section('content')`:

```blade
@push('schema')
    <x-site-schema />
@endpush
```

- [ ] **Step 6: Correr el test y verificar que pasa**

Run: `php artisan test --filter=SiteSchemaTest`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add config/radio.php resources/views/components/site-schema.blade.php resources/views/blog/index.blade.php tests/Feature/SiteSchemaTest.php
git commit -m "feat(seo): WebSite + RadioStation schema with brand name variants"
```

---

### Task 2: Título, canónica y locale de la portada

**Files:**
- Modify: `resources/views/blog/index.blade.php:3` (título) y agregar la sección `canonical_url`
- Modify: `resources/views/layouts/blog.blade.php:21` (`og:locale`)
- Test: `tests/Feature/SiteSchemaTest.php` (agregar tests)

**Interfaces:**
- Consumes: nada de la Task 1.

- [ ] **Step 1: Escribir los tests que fallan**

Agregar al final de `tests/Feature/SiteSchemaTest.php`:

```php
test('home title carries both brand spellings', function () {
    $this->get('/')->assertSee('<title>Radio Algo Más (RadioAlgoMas) | Radio en vivo y noticias</title>', false);
});

test('blog index and home share the home canonical', function () {
    $canonical = '<link rel="canonical" href="' . route('home') . '">';

    $this->get('/blog')->assertSee($canonical, false);
    $this->get('/')->assertSee($canonical, false);
});

test('open graph locale targets US Spanish', function () {
    $this->get('/')->assertSee('<meta property="og:locale" content="es_US">', false);
});
```

- [ ] **Step 2: Correrlos y verificar que fallan**

Run: `php artisan test --filter=SiteSchemaTest`
Expected: los 3 tests nuevos FAIL (título actual `RadioAlgoMas | ...`, canónica de `/blog` es `/blog`, locale `es_ES`).

- [ ] **Step 3: Implementar**

En `resources/views/blog/index.blade.php`, reemplazar la línea 3:

```blade
@section('title', \App\Models\RadioSetting::current()->station_name . ' (' . config('app.name') . ') | Radio en vivo y noticias')
```

y agregar debajo de la línea `meta_description`:

```blade
@section('canonical_url', route('home'))
```

En `resources/views/layouts/blog.blade.php`, cambiar:

```blade
    <meta property="og:locale" content="es_ES">
```

por:

```blade
    <meta property="og:locale" content="es_US">
```

- [ ] **Step 4: Correr la suite completa**

Run: `php artisan test`
Expected: todo PASS (incluye `ArticleCanonicalUrlTest`: las notas siguen usando su propia canónica).

- [ ] **Step 5: Commit**

```bash
git add resources/views/blog/index.blade.php resources/views/layouts/blog.blade.php tests/Feature/SiteSchemaTest.php
git commit -m "feat(seo): brand title, single home canonical, es_US locale"
```

---

### Task 3: Desplegar y validar en Google (manual)

- [ ] Desplegar con el proceso habitual y correr `php artisan view:clear` / `php artisan config:cache` en producción.
- [ ] Probar `https://radioalgomas.com` en https://search.google.com/test/rich-results. Se espera que detecte `WebSite` y `RadioStation` sin errores.
- [ ] Search Console → Inspección de URLs → `https://radioalgomas.com/` → "Solicitar indexación".
- [ ] Search Console → Sitemaps: confirmar que `https://radioalgomas.com/sitemap.xml` está enviado y en estado "Correcto".

---

### Task 4: Señales externas de marca (cliente + tú)

Es lo que más pesa para que Google deje de corregir el nombre.

- [ ] **Google Business Profile**: crear o reclamar "Radio Algo Más" con la categoría "Emisora de radio", la zona de servicio en Maryland y el sitio web `https://radioalgomas.com`.
- [ ] **Redes sociales**: en Facebook, Instagram, YouTube y TikTok, usar el nombre "Radio Algo Más (RadioAlgoMas)" y poner `https://radioalgomas.com` en el campo de sitio web.
- [ ] **Directorios de radio**: corregir el enlace de radiosplay.com (hoy `http://www.radioalgomas.com/`) y darse de alta en myTuner, Streema, TuneIn, Radio Garden y OnlineRadioBox con `https://radioalgomas.com`.
- [ ] Copiar las URLs finales de los perfiles en `config/radio.php` → `same_as`, correr `php artisan test --filter=SiteSchemaTest`, hacer commit (`chore(seo): add official profiles to sameAs`) y desplegar.

---

### Task 5: Medir (a las 2, 4 y 8 semanas)

- [ ] Search Console → Rendimiento → filtros **País = Estados Unidos** y **Consulta contiene "algo"**: anotar impresiones, clics y posición media de `radioalgomas`, `radio algo mas` y `radioalgomas.com`.
- [ ] Pedirle al cliente que repita la búsqueda `radioalgomas` desde Maryland. Éxito = Google ya no muestra "Se muestran resultados de radio gomas" y radioalgomas.com sale primero.
- [ ] Mientras tanto, el cliente puede tocar "Buscar, en cambio, radioalgomas" o buscar `radioalgomas.com`.
