<?php

use App\Filament\Resources\ArticleResource\Pages\ListArticles;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

test('an admin can mark an imported article as newsroom work from the articles list', function () {
    $admin = User::factory()->create();
    foreach (['view_any_article', 'view_article', 'update_article'] as $permission) {
        $admin->givePermissionTo(Permission::create(['name' => $permission, 'guard_name' => 'web']));
    }
    $this->actingAs($admin);

    $category = Category::create(['name' => 'Deportes', 'slug' => 'deportes', 'color' => '#10B981', 'is_active' => true]);
    $article = Article::create([
        'title' => 'Nota importada',
        'slug' => 'nota-importada',
        'excerpt' => 'Resumen',
        'content' => '<p>Texto</p>',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
        'user_id' => $admin->id,
        'is_imported' => true,
        'canonical_url' => 'https://elpais.com/deportes/nota-original.html',
    ]);

    Livewire::test(ListArticles::class)
        ->assertSuccessful()
        ->call('updateTableColumnState', 'is_editorial', (string) $article->getKey(), true);

    expect($article->fresh()->is_editorial)->toBeTrue()
        ->and($article->fresh()->publicCanonicalUrl())->toBe(route('blog.show', 'nota-importada'));
});
