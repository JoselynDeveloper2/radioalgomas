<?php

use App\Models\RadioSetting;
use App\Models\RadioShow;

test('radio settings fall back to config until the admin saves them', function () {
    $settings = RadioSetting::current();

    expect($settings->stream_url)->toBe(config('radio.stream_url'))
        ->and($settings->station_name)->toBe(config('radio.name'))
        ->and($settings->exists)->toBeFalse();
});

test('saving radio settings from the admin persists a single row and overrides the fallback', function () {
    RadioSetting::persist([
        'station_name' => 'Radio Prueba',
        'stream_url' => 'https://example.test/stream.mp3',
        'autoplay' => false,
        'timezone' => 'America/Santo_Domingo',
        'fallback_show_name' => 'Música continua',
        'fallback_show_host' => 'Radio Prueba',
    ]);

    // Guardar de nuevo actualiza la misma fila, no crea una segunda.
    RadioSetting::persist(['station_name' => 'Radio Prueba Actualizada']);

    expect(RadioSetting::query()->count())->toBe(1);

    $settings = RadioSetting::current();
    expect($settings->station_name)->toBe('Radio Prueba Actualizada')
        ->and($settings->stream_url)->toBe('https://example.test/stream.mp3')
        ->and($settings->autoplay)->toBeFalse();
});

test('radio schedule falls back to config when no shows are configured', function () {
    expect(RadioShow::schedule()->all())->toBe(config('radio.schedule'));
});

test('radio schedule uses active shows from the admin, ordered by start time and excluding inactive ones', function () {
    RadioShow::create(['name' => 'Tardes', 'host' => 'Ana', 'start_time' => '15:30', 'end_time' => '18:00', 'is_active' => true]);
    RadioShow::create(['name' => 'Oculto', 'host' => 'Nadie', 'start_time' => '05:00', 'end_time' => '06:00', 'is_active' => false]);
    RadioShow::create(['name' => 'Mañanas', 'host' => 'Luis', 'start_time' => '06:00', 'end_time' => '10:00', 'is_active' => true]);

    expect(RadioShow::schedule()->pluck('name')->all())->toBe(['Mañanas', 'Tardes']);
});

test('radio lineup reports the current and next show, and falls back outside the schedule', function () {
    RadioShow::create(['name' => 'Mañanas', 'host' => 'Luis', 'start_time' => '06:00', 'end_time' => '10:00']);
    RadioShow::create(['name' => 'Mesa', 'host' => 'Ana', 'start_time' => '10:00', 'end_time' => '13:00']);

    $this->travelTo(now(config('radio.timezone'))->setTime(7, 30));
    $lineup = RadioShow::lineup();
    expect($lineup['current']['name'])->toBe('Mañanas')
        ->and($lineup['next']['name'])->toBe('Mesa')
        ->and($lineup['show']['name'])->toBe('Mañanas');

    $this->travelTo(now(config('radio.timezone'))->setTime(23, 0));
    $lineup = RadioShow::lineup();
    expect($lineup['current'])->toBeNull()
        ->and($lineup['show']['name'])->toBe(config('radio.fallback_show.name'))
        ->and($lineup['upcoming']->pluck('name')->all())->toBe(['Mañanas', 'Mesa']);
});

test('radio show times are normalized to HH:MM even with seconds from the time picker', function () {
    $show = RadioShow::create(['name' => 'Test', 'host' => 'Host', 'start_time' => '06:00:00', 'end_time' => '10:00:00']);

    expect($show->start_time)->toBe('06:00')
        ->and($show->end_time)->toBe('10:00');
});
