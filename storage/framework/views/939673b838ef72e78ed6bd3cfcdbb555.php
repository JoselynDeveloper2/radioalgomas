<?php $__env->startSection('title', $article->meta_title ?: $article->title); ?>
<?php $__env->startSection('meta_description', $article->meta_description ?: $article->excerpt); ?>
<?php $__env->startSection('meta_keywords', $article->meta_keywords); ?>
<?php $__env->startSection('canonical_url', $article->canonical_url ?: route('blog.show', $article->slug)); ?>

<?php $__env->startSection('og_type', 'article'); ?>
<?php $__env->startSection('og_title', $article->og_title ?: $article->title); ?>
<?php $__env->startSection('og_description', $article->og_description ?: $article->excerpt); ?>
<?php $__env->startSection('og_image', $article->og_image ? Storage::url($article->og_image) : ($article->featured_image ? Storage::url($article->featured_image) : '')); ?>

<?php $__env->startSection('twitter_title', $article->og_title ?: $article->title); ?>
<?php $__env->startSection('twitter_description', $article->og_description ?: $article->excerpt); ?>
<?php $__env->startSection('twitter_image', $article->og_image ? Storage::url($article->og_image) : ($article->featured_image ? Storage::url($article->featured_image) : '')); ?>

<?php $__env->startPush('schema'); ?>
<?php if (isset($component)) { $__componentOriginalc87a372b2c133bb0cf1ddca5ea69be3d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc87a372b2c133bb0cf1ddca5ea69be3d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.schema-markup','data' => ['article' => $article]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('schema-markup'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['article' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($article)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc87a372b2c133bb0cf1ddca5ea69be3d)): ?>
<?php $attributes = $__attributesOriginalc87a372b2c133bb0cf1ddca5ea69be3d; ?>
<?php unset($__attributesOriginalc87a372b2c133bb0cf1ddca5ea69be3d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc87a372b2c133bb0cf1ddca5ea69be3d)): ?>
<?php $component = $__componentOriginalc87a372b2c133bb0cf1ddca5ea69be3d; ?>
<?php unset($__componentOriginalc87a372b2c133bb0cf1ddca5ea69be3d); ?>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <?php if (isset($component)) { $__componentOriginale19f62b34dfe0bfdf95075badcb45bc2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale19f62b34dfe0bfdf95075badcb45bc2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumb','data' => ['items' => [
                [
                    'label' => $article->category->name,
                    'url' => route('blog.category', $article->category->slug)
                ],
                [
                    'label' => Str::limit($article->title, 50),
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
                    'label' => $article->category->name,
                    'url' => route('blog.category', $article->category->slug)
                ],
                [
                    'label' => Str::limit($article->title, 50),
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

        <!-- Article Header -->
        <header class="mb-8">
            <!-- Category Badge -->
            <div class="mb-4">
                <a href="<?php echo e(route('blog.category', $article->category->slug)); ?>" class="inline-block px-4 py-2 text-sm font-semibold rounded-full transition-colors" style="background-color: <?php echo e($article->category->color); ?>20; color: <?php echo e($article->category->color); ?>; border: 1px solid <?php echo e($article->category->color); ?>40;">
                    <?php echo e($article->category->name); ?>

                </a>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                <?php echo e($article->title); ?>

            </h1>

            <!-- Excerpt -->
            <?php if($article->excerpt): ?>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
                <?php echo e($article->excerpt); ?>

            </p>
            <?php endif; ?>

            <!-- Article Meta -->
            <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600 dark:text-gray-400 mb-6">
                <!-- Author -->
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            <?php echo e(substr($article->user->name, 0, 1)); ?>

                        </span>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white"><?php echo e($article->user->name); ?></div>
                        <?php if($article->user->bio): ?>
                        <div class="text-xs"><?php echo e(Str::limit($article->user->bio, 50)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Publication Date -->
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <time datetime="<?php echo e($article->published_at->toISOString()); ?>">
                        <?php echo e($article->published_at->format('d M Y')); ?>

                    </time>
                </div>

                <!-- Reading Time -->
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?php echo e($article->reading_time); ?> min lectura
                </div>

                <!-- Views -->
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <?php echo e(number_format($article->views_count)); ?> vistas
                </div>
            </div>

            <!-- Social Share Buttons -->
            <div class="flex items-center space-x-4 mb-8">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Compartir:</span>
                <a href="https://twitter.com/intent/tweet?text=<?php echo e(urlencode($article->title)); ?>&url=<?php echo e(urlencode(route('blog.show', $article->slug))); ?>" target="_blank" class="flex items-center px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                    </svg>
                    Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(route('blog.show', $article->slug))); ?>" target="_blank" class="flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                </a>
                <a href="https://wa.me/?text=<?php echo e(urlencode($article->title . ' ' . route('blog.show', $article->slug))); ?>" target="_blank" class="flex items-center px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                    WhatsApp
                </a>
            </div>
        </header>

        <!-- Featured Image -->
        <?php if($article->featured_image): ?>
        <div class="mb-8">
            <img src="<?php echo e(Storage::url($article->featured_image)); ?>" alt="<?php echo e($article->title); ?>" class="w-auto h-auto rounded-lg shadow-lg">
        </div>
        <?php endif; ?>

        <!-- Article Content -->
        <article class="prose prose-lg dark:prose-invert max-w-none mb-12">
            <?php echo $article->content; ?>

        </article>

        <!-- Tags -->
        <?php if($article->tags->count() > 0): ?>
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Etiquetas</h3>
            <div class="flex flex-wrap gap-2">
                <?php $__currentLoopData = $article->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('blog.tag', $tag->slug)); ?>" class="inline-block px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                    <?php echo e($tag->name); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Author Bio -->
        <?php if($article->user->bio): ?>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-12">
            <div class="flex items-start">
                <div class="w-16 h-16 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                    <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        <?php echo e(substr($article->user->name, 0, 1)); ?>

                    </span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"><?php echo e($article->user->name); ?></h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-3"><?php echo e($article->user->bio); ?></p>
                    <div class="flex space-x-4">
                        <?php if($article->user->website): ?>
                        <a href="<?php echo e($article->user->website); ?>" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                            Sitio web
                        </a>
                        <?php endif; ?>
                        <?php if($article->user->twitter): ?>
                        <a href="https://twitter.com/<?php echo e($article->user->twitter); ?>" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                            Twitter
                        </a>
                        <?php endif; ?>
                        <?php if($article->user->linkedin): ?>
                        <a href="<?php echo e($article->user->linkedin); ?>" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                            LinkedIn
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Related Articles -->
        <?php if($relatedArticles->count() > 0): ?>
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Artículos Relacionados</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php $__currentLoopData = $relatedArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedArticle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <?php if($relatedArticle->featured_image): ?>
                    <div class="h-32 overflow-hidden">
                        <img src="<?php echo e(Storage::url($relatedArticle->featured_image)); ?>" alt="<?php echo e($relatedArticle->title); ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <div class="flex items-center mb-2">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full" style="background-color: <?php echo e($relatedArticle->category->color); ?>20; color: <?php echo e($relatedArticle->category->color); ?>">
                                <?php echo e($relatedArticle->category->name); ?>

                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2 hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-2">
                            <a href="<?php echo e(route('blog.show', $relatedArticle->slug)); ?>">
                                <?php echo e($relatedArticle->title); ?>

                            </a>
                        </h3>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mb-2 line-clamp-2">
                            <?php echo e($relatedArticle->excerpt); ?>

                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span><?php echo e($relatedArticle->published_at->diffForHumans()); ?></span>
                            <span><?php echo e($relatedArticle->reading_time); ?> min</span>
                        </div>
                    </div>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Copy URL to clipboard
    function copyUrl() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('URL copiada al portapapeles');
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.blog', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/blog/show.blade.php ENDPATH**/ ?>