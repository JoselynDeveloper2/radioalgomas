<?php

use App\Filament\Pages\RadioSettingsPage;
use App\Filament\Resources\RadioShowResource\Pages\CreateRadioShow;
use App\Filament\Resources\RadioShowResource\Pages\ListRadioShows;
use App\Models\RadioSetting;
use App\Models\RadioShow;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('radio settings page loads and saves to the database', function () {
    Livewire::test(RadioSettingsPage::class)
        ->assertFormSet(['stream_url' => config('radio.stream_url')])
        ->fillForm([
            'station_name' => 'Radio Prueba',
            'stream_url' => 'https://example.test/stream.mp3',
            'autoplay' => true,
            'timezone' => 'America/Santo_Domingo',
            'fallback_show_name' => 'Música continua',
            'fallback_show_host' => 'Radio Prueba',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(RadioSetting::current()->station_name)->toBe('Radio Prueba');
});

test('radio shows list page renders for an authenticated admin', function () {
    Livewire::test(ListRadioShows::class)->assertSuccessful();
});

test('a radio show can be created from the admin panel', function () {
    Livewire::test(CreateRadioShow::class)
        ->fillForm([
            'name' => 'Mañanas Informativas',
            'host' => 'Ana Pérez',
            'start_time' => '06:00',
            'end_time' => '10:00',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(RadioShow::query()->where('name', 'Mañanas Informativas')->exists())->toBeTrue();
});
