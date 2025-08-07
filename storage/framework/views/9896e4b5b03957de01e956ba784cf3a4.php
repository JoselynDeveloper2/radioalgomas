<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'article',
    'featured' => false,
    'showAuthor' => true,
    'showExcerpt' => true,
    'showCategory' => true,
    'layout' => 'default' // default, compact, minimal
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'article',
    'featured' => false,
    'showAuthor' => true,
    'showExcerpt' => true,
    'showCategory' => true,
    'layout' => 'default' // default, compact, minimal
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $cardClasses = match($layout) {
        'featured' => 'lg:col-span-2 lg:row-span-2',
        'compact' => 'flex flex-row',
        'minimal' => 'border-b border-gray-200 dark:border-gray-700 pb-4',
        default => ''
    };
    
    $imageClasses = match($layout) {
        'featured' => 'h-64 lg:h-80',
        'compact' => 'w-24 h-24 flex-shrink-0',
        'minimal' => 'w-16 h-16 flex-shrink-0',
        default => 'h-48'
    };
    
    $titleClasses = match($layout) {
        'featured' => 'text-2xl lg:text-3xl',
        'compact' => 'text-lg',
        'minimal' => 'text-base',
        default => 'text-xl'
    };
?>

<article class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 <?php echo e($cardClasses); ?>">
    <!-- Article Image -->
    <div class="<?php echo e($imageClasses); ?> overflow-hidden relative <?php echo e($layout === 'compact' ? 'rounded-l-xl mr-4' : ''); ?>">
        <?php if($article->featured_image): ?>
            <img src="<?php echo e(Storage::url($article->featured_image)); ?>" 
                 alt="<?php echo e($article->title); ?>" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        <?php else: ?>
            <div class="w-full h-full bg-gradient-to-br from-tucanaltv-blue to-tucanaltv-blue-dark flex items-center justify-center">
                <svg class="w-8 h-8 text-white opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
            </div>
        <?php endif; ?>
        
        <!-- Image overlay effects -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
        
        <!-- Category badge on image -->
        <?php if($showCategory && $layout !== 'minimal'): ?>
        <div class="absolute top-3 left-3">
            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-white/90 backdrop-blur-sm" style="color: <?php echo e($article->category->color ?? '#007bff'); ?>;">
                <?php echo e($article->category->name ?? 'Sin categoría'); ?>

            </span>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Article Content -->
    <div class="p-4 <?php echo e($layout === 'featured' ? 'p-6' : ''); ?> <?php echo e($layout === 'compact' ? 'flex-1' : ''); ?>">
        <!-- Meta information -->
        <div class="flex items-center justify-between mb-3 text-sm text-gray-500 dark:text-gray-400">
            <?php if($showCategory && $layout === 'minimal'): ?>
            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full" style="background-color: <?php echo e($article->category->color ?? '#007bff'); ?>20; color: <?php echo e($article->category->color ?? '#007bff'); ?>;">
                <?php echo e($article->category->name ?? 'Sin categoría'); ?>

            </span>
            <?php else: ?>
            <span><?php echo e($article->published_at->diffForHumans()); ?></span>
            <?php endif; ?>
            <span class="text-xs"><?php echo e($article->reading_time ?? '3'); ?> min lectura</span>
        </div>
        
        <!-- Article Title -->
        <h3 class="<?php echo e($titleClasses); ?> font-bold text-gray-900 dark:text-white mb-3 leading-tight">
            <a href="<?php echo e(route('blog.show', $article->slug)); ?>" 
               class="hover:text-tucanaltv-blue transition-colors group-hover:text-tucanaltv-blue <?php echo e($layout === 'minimal' ? 'line-clamp-2' : ''); ?>">
                <?php echo e($article->title); ?>

            </a>
        </h3>
        
        <!-- Article Excerpt -->
        <?php if($showExcerpt && $article->excerpt && $layout !== 'minimal'): ?>
        <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-3 leading-relaxed <?php echo e($layout === 'featured' ? 'text-lg' : ''); ?>">
            <?php echo e($article->excerpt); ?>

        </p>
        <?php endif; ?>
        
        <!-- Article Footer -->
        <div class="flex items-center justify-between <?php echo e($layout === 'minimal' ? 'mt-2' : 'pt-4 border-t border-gray-100 dark:border-gray-700'); ?>">
            <?php if($showAuthor): ?>
            <div class="flex items-center">
                <div class="w-<?php echo e($layout === 'minimal' ? '6' : '8'); ?> h-<?php echo e($layout === 'minimal' ? '6' : '8'); ?> bg-gradient-to-br from-tucanaltv-blue to-tucanaltv-blue-dark rounded-full flex items-center justify-center mr-3">
                    <span class="text-<?php echo e($layout === 'minimal' ? 'xs' : 'sm'); ?> font-semibold text-white">
                        <?php echo e(substr($article->user->name ?? 'A', 0, 1)); ?>

                    </span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <?php echo e($article->user->name ?? 'Anónimo'); ?>

                    </span>
                    <?php if($layout !== 'minimal'): ?>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <?php echo e($article->published_at->diffForHumans()); ?>

                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Views counter -->
            <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <?php echo e(number_format($article->views_count ?? 0)); ?>

                </div>
            </div>
        </div>
    </div>
</article><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/components/article-card.blade.php ENDPATH**/ ?>