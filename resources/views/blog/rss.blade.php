<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ url('/') }}</link>
        <description>Las últimas noticias y actualidad de {{ config('app.name') }}</description>
        <language>es-ES</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ route('blog.rss') }}" rel="self" type="application/rss+xml" />
        <generator>Laravel {{ app()->version() }}</generator>
        <webMaster>{{ config('mail.from.address') }} ({{ config('app.name') }})</webMaster>
        <managingEditor>{{ config('mail.from.address') }} ({{ config('app.name') }})</managingEditor>
        <copyright>Copyright {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</copyright>
        <category>Noticias</category>
        <ttl>60</ttl>
        <image>
            <url>{{ asset('images/logo.png') }}</url>
            <title>{{ config('app.name') }}</title>
            <link>{{ url('/') }}</link>
            <width>144</width>
            <height>144</height>
            <description>Logo de {{ config('app.name') }}</description>
        </image>

        @foreach($articles as $article)
        <item>
            <title><![CDATA[{{ $article->title }}]]></title>
            <link>{{ route('blog.show', $article->slug) }}</link>
            <guid isPermaLink="true">{{ route('blog.show', $article->slug) }}</guid>
            <description><![CDATA[{{ $article->excerpt }}]]></description>
            <content:encoded><![CDATA[{!! $article->content !!}]]></content:encoded>
            <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
            <dc:creator><![CDATA[{{ $article->user->name }}]]></dc:creator>
            <category><![CDATA[{{ $article->category->name }}]]></category>
            @foreach($article->tags as $tag)
            <category><![CDATA[{{ $tag->name }}]]></category>
            @endforeach
            @if($article->featured_image)
            <enclosure url="{{ $article->featured_image }}" type="image/jpeg" />
            @endif
        </item>
        @endforeach
    </channel>
</rss>