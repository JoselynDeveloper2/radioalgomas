<?php $__env->startSection('title', 'TuCanalTV - Noticias y Actualidad'); ?>
<?php $__env->startSection('meta_description', 'Mantente informado con las últimas noticias locales, deportes, entretenimiento y más en
    TuCanalTV.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 pb-8">
        <!-- Reproductor de Video en Vivo -->
        <section class="mb-8">
            <?php if (isset($component)) { $__componentOriginalb1f8fa33ff8600d3f2a6183c89419c25 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb1f8fa33ff8600d3f2a6183c89419c25 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.video-player','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('video-player'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb1f8fa33ff8600d3f2a6183c89419c25)): ?>
<?php $attributes = $__attributesOriginalb1f8fa33ff8600d3f2a6183c89419c25; ?>
<?php unset($__attributesOriginalb1f8fa33ff8600d3f2a6183c89419c25); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb1f8fa33ff8600d3f2a6183c89419c25)): ?>
<?php $component = $__componentOriginalb1f8fa33ff8600d3f2a6183c89419c25; ?>
<?php unset($__componentOriginalb1f8fa33ff8600d3f2a6183c89419c25); ?>
<?php endif; ?>
        </section>

        <!-- Hero Section con Artículos Destacados -->
        <?php if($featuredArticles->count() > 0): ?>
            <section class="mb-12">
                <div class="tucanaltv-featured-title">
                    <h2>Noticias Destacadas</h2>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <?php $__currentLoopData = $featuredArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if (isset($component)) { $__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.article-card','data' => ['article' => $article,'layout' => $index === 0 ? 'featured' : 'default','showAuthor' => true,'showExcerpt' => true,'showCategory' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('article-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['article' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($article),'layout' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($index === 0 ? 'featured' : 'default'),'show-author' => true,'show-excerpt' => true,'show-category' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15)): ?>
<?php $attributes = $__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15; ?>
<?php unset($__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15)): ?>
<?php $component = $__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15; ?>
<?php unset($__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Contenido Principal -->
            <main class="lg:col-span-3">
                <!-- Filtros y Búsqueda -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                    <form method="GET" action="<?php echo e(route('blog.index')); ?>" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                                placeholder="Buscar noticias..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="tucanaltv-btn-primary">
                                Buscar
                            </button>
                            <?php if(request()->hasAny(['search', 'category', 'tag'])): ?>
                                <a href="<?php echo e(route('blog.index')); ?>" class="tucanaltv-btn-secondary">
                                    Limpiar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Lista de Artículos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php if (isset($component)) { $__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.article-card','data' => ['article' => $article,'layout' => 'default','showAuthor' => true,'showExcerpt' => true,'showCategory' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('article-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['article' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($article),'layout' => 'default','show-author' => true,'show-excerpt' => true,'show-category' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15)): ?>
<?php $attributes = $__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15; ?>
<?php unset($__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15)): ?>
<?php $component = $__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15; ?>
<?php unset($__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-span-2 text-center py-16">
                            <div class="text-gray-500 dark:text-gray-400">
                                <div
                                    class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-semibold mb-3 text-gray-900 dark:text-white">No se encontraron
                                    artículos</h3>
                                <p class="text-lg mb-6 max-w-md mx-auto">Intenta con otros términos de búsqueda o explora
                                    nuestras categorías disponibles.</p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <a href="<?php echo e(route('blog.index')); ?>" class="tucanaltv-btn-primary">
                                        Ver todos los artículos
                                    </a>
                                    <button
                                        onclick="document.getElementById('searchBar').classList.remove('hidden'); document.querySelector('#searchBar input').focus();"
                                        class="tucanaltv-btn-secondary">
                                        Nueva búsqueda
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Paginación -->
                <?php if($articles->hasPages()): ?>
                    <div class="mt-8">
                        <?php echo e($articles->links()); ?>

                    </div>
                <?php endif; ?>
            </main>

            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <!-- Categorías -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Categorías</h3>
                    <ul class="space-y-2">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="<?php echo e(route('blog.category', $category->slug)); ?>"
                                    class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3"
                                            style="background-color: <?php echo e($category->color); ?>"></div>
                                        <span class="text-gray-700 dark:text-gray-300"><?php echo e($category->name); ?></span>
                                    </div>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($category->articles_count); ?></span>
                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>

                <!-- Etiquetas Populares -->
                <?php if($popularTags->count() > 0): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Etiquetas Populares</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $popularTags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('blog.tag', $tag->slug)); ?>"
                                    class="inline-block px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                    <?php echo e($tag->name); ?>

                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400 ml-1">(<?php echo e($tag->articles_count); ?>)</span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.blog', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/blog/index.blade.php ENDPATH**/ ?>