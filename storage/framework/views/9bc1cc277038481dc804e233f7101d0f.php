<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title><?php echo e(config('app.name')); ?></title>
        <link><?php echo e(url('/')); ?></link>
        <description>Las últimas noticias y actualidad de <?php echo e(config('app.name')); ?></description>
        <language>es-ES</language>
        <lastBuildDate><?php echo e(now()->toRssString()); ?></lastBuildDate>
        <atom:link href="<?php echo e(route('blog.rss')); ?>" rel="self" type="application/rss+xml" />
        <generator>Laravel <?php echo e(app()->version()); ?></generator>
        <webMaster><?php echo e(config('mail.from.address')); ?> (<?php echo e(config('app.name')); ?>)</webMaster>
        <managingEditor><?php echo e(config('mail.from.address')); ?> (<?php echo e(config('app.name')); ?>)</managingEditor>
        <copyright>Copyright <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. Todos los derechos reservados.</copyright>
        <category>Noticias</category>
        <ttl>60</ttl>
        <image>
            <url><?php echo e(asset('images/logo.png')); ?></url>
            <title><?php echo e(config('app.name')); ?></title>
            <link><?php echo e(url('/')); ?></link>
            <width>144</width>
            <height>144</height>
            <description>Logo de <?php echo e(config('app.name')); ?></description>
        </image>

        <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <item>
            <title><![CDATA[<?php echo e($article->title); ?>]]></title>
            <link><?php echo e(route('blog.show', $article->slug)); ?></link>
            <guid isPermaLink="true"><?php echo e(route('blog.show', $article->slug)); ?></guid>
            <description><![CDATA[<?php echo e($article->excerpt); ?>]]></description>
            <content:encoded><![CDATA[<?php echo $article->content; ?>]]></content:encoded>
            <pubDate><?php echo e($article->published_at->toRssString()); ?></pubDate>
            <dc:creator><![CDATA[<?php echo e($article->user->name); ?>]]></dc:creator>
            <category><![CDATA[<?php echo e($article->category->name); ?>]]></category>
            <?php $__currentLoopData = $article->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <category><![CDATA[<?php echo e($tag->name); ?>]]></category>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($article->featured_image): ?>
            <enclosure url="<?php echo e($article->featured_image); ?>" type="image/jpeg" />
            <?php endif; ?>
        </item>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </channel>
</rss><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/blog/rss.blade.php ENDPATH**/ ?>