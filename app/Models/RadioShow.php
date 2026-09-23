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
}
