<?php

use App\Models\Article;

test('canonical URL recalculates stale self references and keeps external sources', function (?string $stored, string $expected) {
    $article = (new Article())->forceFill(['slug' => 'mi-nota', 'canonical_url' => $stored]);

    expect($article->publicCanonicalUrl())->toBe(str_replace('SELF', route('blog.show', 'mi-nota'), $expected));
})->with([
    'sin canónica' => [null, 'SELF'],
    'dominio viejo del clon' => ['https://tucanaltv.test/blog/mi-nota', 'SELF'],
    'ruta inexistente /articulos' => ['https://otro.test/articulos/mi-nota', 'SELF'],
    'fuente original del RSS' => ['https://www.diariolibre.com/deportes/otra-nota-GD123', 'https://www.diariolibre.com/deportes/otra-nota-GD123'],
]);

test('seo canonical override takes priority over the auto canonical', function () {
    $article = (new Article())->forceFill([
        'slug' => 'mi-nota',
        'seo_canonical_url' => 'https://fuente.test/original',
        'canonical_url' => 'https://tucanaltv.test/articulos/mi-nota',
    ]);

    expect($article->publicCanonicalUrl())->toBe('https://fuente.test/original');
});
