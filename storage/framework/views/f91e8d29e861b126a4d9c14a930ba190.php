<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <!-- Homepage -->
    <url>
        <loc><?php echo e(url('/')); ?></loc>
        <lastmod><?php echo e(now()->toISOString()); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Categories -->
    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e(route('blog.category', $category->slug)); ?></loc>
        <lastmod><?php echo e($category->updated_at ? $category->updated_at->toISOString() : now()->toISOString()); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <!-- Tags -->
    <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e(route('blog.tag', $tag->slug)); ?></loc>
        <lastmod><?php echo e($tag->updated_at ? $tag->updated_at->toISOString() : now()->toISOString()); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <!-- Articles -->
    <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <url>
        <loc><?php echo e(route('blog.show', $article->slug)); ?></loc>
        <lastmod><?php echo e($article->updated_at ? $article->updated_at->toISOString() : now()->toISOString()); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
        
        <!-- Google News Sitemap -->
        <news:news>
            <news:publication>
                <news:name><?php echo e(config('app.name')); ?></news:name>
                <news:language>es</news:language>
            </news:publication>
            <news:publication_date><?php echo e($article->published_at ? $article->published_at->toISOString() : $article->updated_at->toISOString()); ?></news:publication_date>
            <news:title><![CDATA[<?php echo e($article->title); ?>]]></news:title>
            <news:keywords><?php echo e($article->tags->pluck('name')->implode(', ')); ?></news:keywords>
        </news:news>
        
        <!-- Image Sitemap -->
        <?php if($article->featured_image): ?>
        <image:image>
            <image:loc><?php echo e($article->featured_image); ?></image:loc>
            <image:title><![CDATA[<?php echo e($article->title); ?>]]></image:title>
            <image:caption><![CDATA[<?php echo e($article->excerpt); ?>]]></image:caption>
        </image:image>
        <?php endif; ?>
    </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</urlset><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/blog/sitemap.blade.php ENDPATH**/ ?>