<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'items' => [],
    'theme' => 'light' // light or dark
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
    'items' => [],
    'theme' => 'light' // light or dark
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $textClasses = $theme === 'dark' 
        ? 'text-blue-100 hover:text-white' 
        : 'text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white';
    
    $separatorClasses = $theme === 'dark'
        ? 'text-blue-200'
        : 'text-gray-400';
        
    $currentClasses = $theme === 'dark'
        ? 'text-blue-200'
        : 'text-gray-500 dark:text-gray-400';
?>

<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm">
        <!-- Home link -->
        <li>
            <a href="<?php echo e(route('home')); ?>" class="flex items-center <?php echo e($textClasses); ?> transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                </svg>
                Inicio
            </a>
        </li>
        
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li>
                <svg class="w-4 h-4 <?php echo e($separatorClasses); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </li>
            <li <?php if($loop->last): ?> aria-current="page" <?php endif; ?>>
                <?php if($loop->last): ?>
                    <span class="<?php echo e($currentClasses); ?> truncate max-w-xs">
                        <?php echo e($item['label']); ?>

                    </span>
                <?php else: ?>
                    <a href="<?php echo e($item['url']); ?>" class="<?php echo e($textClasses); ?> transition-colors">
                        <?php echo e($item['label']); ?>

                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</nav><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>