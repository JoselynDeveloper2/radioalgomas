<?php $__env->startSection('title', $category->meta_title ?: $category->name . ' - ' . config('app.name')); ?>
<?php $__env->startSection('meta_description', $category->meta_description ?: 'Noticias de ' . $category->name . ' en ' . config('app.name')); ?>
<?php $__env->startSection('meta_keywords', $category->meta_keywords ?: $category->name . ', noticias, actualidad'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <!-- Category Header -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4" style="background-color: <?php echo e($category->color); ?>20">
            <?php if($category->icon): ?>
                <?php echo e(svg('heroicon-o-' . $category->icon, 'w-6 h-6', ['style' => 'color: ' . $category->color])); ?>
            <?php else: ?>
            <div class="w-8 h-8 rounded-full" style="background-color: <?php echo e($category->color); ?>"></div>
            <?php endif; ?>
        </div>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            <?php echo e($category->name); ?>

        </h1>
        <?php if($category->description): ?>
        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            <?php echo e($category->description); ?>

        </p>
        <?php endif; ?>
        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            <?php echo e($articles->total()); ?> <?php echo e(Str::plural('artículo', $articles->total())); ?>

        </div>
    </div>

    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
            [
                'label' => $category->name,
                'url' => null
            ]
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            [
                'label' => $category->name,
                'url' => null
            ]
        ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $attributes = $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2)): ?>
<?php $component = $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2; ?>
<?php unset($__componentOriginale19f62b34dfe0bfdf95075badcb45bc2); ?>
<?php endif; ?>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <main class="lg:col-span-3">
            <!-- Articles Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if (isset($component)) { $__componentOriginal2ef36d4355cd7834c6b42ce99ba2ff15 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ef36d4355cd7834c6b42ce99ba2ff15 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.article-card','data' => ['article' => $article,'layout' => 'default','showAuthor' => true,'showExcerpt' => true,'showCategory' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('article-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['article' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($article),'layout' => 'default','show-author' => true,'show-excerpt' => true,'show-category' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
                <div class="col-span-2 text-center py-12">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold mb-2">No hay artículos en esta categoría</h3>
                        <p>Vuelve pronto para ver nuevo contenido.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if($articles->hasPages()): ?>
            <div class="mt-8">
                <?php echo e($articles->links()); ?>

            </div>
            <?php endif; ?>
        </main>

        <!-- Sidebar -->
        <aside class="lg:col-span-1">
            <!-- Other Categories -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Otras Categorías</h3>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $categories->where('id', '!=', $category->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $otherCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e(route('blog.category', $otherCategory->slug)); ?>" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: <?php echo e($otherCategory->color); ?>"></div>
                                <span class="text-gray-700 dark:text-gray-300"><?php echo e($otherCategory->name); ?></span>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($otherCategory->articles_count); ?></span>
                        </a>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            <!-- Category Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Estadísticas</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total de artículos</span>
                        <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($articles->total()); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Última actualización</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            <?php if($articles->count() > 0): ?>
                            <?php echo e($articles->first()->published_at->diffForHumans()); ?>

                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.blog', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/blog/category.blade.php ENDPATH**/ ?>