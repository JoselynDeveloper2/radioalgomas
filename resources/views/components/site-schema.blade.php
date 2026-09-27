@php
    $name = \App\Models\RadioSetting::current()->station_name;
    $alternateNames = config('radio.alternate_names');

    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => route('home') . '#website',
                'url' => route('home'),
                'name' => $name,
                'alternateName' => $alternateNames,
                'inLanguage' => 'es',
            ],
            array_filter([
                '@type' => 'RadioStation',
                '@id' => route('home') . '#station',
                'url' => route('home'),
                'name' => $name,
                'alternateName' => $alternateNames,
                'logo' => asset('images/logo.png'),
                'sameAs' => config('radio.same_as') ?: null,
            ]),
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
