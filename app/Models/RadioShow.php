<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RadioShow extends Model
{
    protected $fillable = [
        'name',
        'host',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // El TimePicker de Filament entrega "HH:MM:SS"; nos quedamos solo con "HH:MM".
    public function setStartTimeAttribute(?string $value): void
    {
        $this->attributes['start_time'] = $value === null ? null : substr($value, 0, 5);
    }

    public function setEndTimeAttribute(?string $value): void
    {
        $this->attributes['end_time'] = $value === null ? null : substr($value, 0, 5);
    }

    /**
     * Parrilla activa ordenada por hora. Mientras el cliente no cargue
     * programas desde el administrador, cae en config/radio.php.
     */
    public static function schedule(): Collection
    {
        $shows = static::query()->where('is_active', true)->orderBy('start_time')->get();

        if ($shows->isEmpty()) {
            return collect(config('radio.schedule'));
        }

        return $shows->map(fn (self $show) => [
            'start' => $show->start_time,
            'end' => $show->end_time,
            'name' => $show->name,
            'host' => $show->host,
        ]);
    }

    /**
     * Qué suena ahora y qué sigue, en la hora local de la emisora.
     * Fuera de la parrilla, "upcoming" muestra los primeros programas del día siguiente.
     */
    public static function lineup(int $take = 4): array
    {
        $settings = RadioSetting::current();
        $now = now($settings->timezone)->format('H:i');
        $schedule = static::schedule()->values();

        $index = $schedule->search(fn ($s) => $s['start'] <= $now && $now < $s['end']);
        $current = $index === false ? null : $schedule[$index];

        $upcoming = $current
            ? $schedule->slice($index)->take($take)
            : $schedule->filter(fn ($s) => $s['start'] > $now)->take($take);
        if ($upcoming->isEmpty()) {
            $upcoming = $schedule->take($take);
        }

        return [
            'settings' => $settings,
            'current' => $current,
            'show' => $current ?? ['name' => $settings->fallback_show_name, 'host' => $settings->fallback_show_host],
            'next' => $upcoming->first(fn ($s) => $s !== $current),
            'upcoming' => $upcoming->values(),
        ];
    }
}
