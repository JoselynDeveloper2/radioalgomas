# Reglas del Proyecto - Blog de Noticias

## Stack Tecnológico
- Laravel 11
- Filament v3
- MySQL/PostgreSQL
- Tailwind CSS (incluido con Filament)

## Estructura de Modelos
- Article (noticia principal)
- Category (categorías de noticias)
- Tag (etiquetas)
- User (autores/editores)

## Convenciones de Código
- Usar Form Requests para validaciones
- Implementar Observer patterns para SEO automático
- Slugs automáticos con Spatie/laravel-sluggable
- Timestamps en español para frontend

## SEO Requirements
- Meta title, description automáticos
- Open Graph tags
- Schema.org JSON-LD
- Sitemap dinámico
- RSS feeds automáticos

## Filament Específico
- Usar Resource classes para CRUD
- Implementar custom actions para publicación
- Rich text editor para contenido
- Image upload con optimización automática
