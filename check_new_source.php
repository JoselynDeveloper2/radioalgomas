<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Obtener el último artículo importado
$article = \App\Models\Article::latest()->first();

echo "Article ID: " . $article->id . PHP_EOL;
echo "Title: " . $article->title . PHP_EOL;
echo "Source URL: " . $article->source_url . PHP_EOL;
echo PHP_EOL;
echo "Last 500 characters of content:" . PHP_EOL;
echo "=" . str_repeat("=", 60) . PHP_EOL;
echo substr($article->content, -500);
echo PHP_EOL;
echo "=" . str_repeat("=", 60) . PHP_EOL;