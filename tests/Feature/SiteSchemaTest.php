<?php

function siteLdJson(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    return array_map(fn ($json) => json_decode($json, true), $m[1]);
}

test('home publishes WebSite and RadioStation schema with brand name variants', function () {
    $graph = collect(siteLdJson($this->get('/')->getContent()))->pluck('@graph')->filter()->flatten(1);
    $website = $graph->firstWhere('@type', 'WebSite');
    $station = $graph->firstWhere('@type', 'RadioStation');

    expect($website['url'])->toBe(route('home'))
        ->and($website['name'])->toBe('Radio Algo Más')
        ->and($website['alternateName'])->toContain('RadioAlgoMas', 'radioalgomas')
        ->and($station['url'])->toBe(route('home'))
        ->and($station['alternateName'])->toContain('radioalgomas.com')
        ->and($station['logo'])->toBe(asset('images/logo.png'));
});
