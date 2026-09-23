<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadioSetting extends Model
{
    protected $fillable = [
        'station_name',
        'stream_url',
        'autoplay',
        'timezone',
        'fallback_show_name',
        'fallback_show_host',
    ];

    protected $casts = [
        'autoplay' => 'boolean',
    ];

    /**
     * Fila única de configuración. Mientras el cliente no la guarde desde el
     * administrador, cae en los valores por defecto de config/radio.php (.env).
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'station_name' => config('radio.name'),
            'stream_url' => config('radio.stream_url'),
            'autoplay' => config('radio.autoplay'),
            'timezone' => config('radio.timezone'),
            'fallback_show_name' => config('radio.fallback_show.name'),
            'fallback_show_host' => config('radio.fallback_show.host'),
        ]);
    }

    public static function persist(array $data): self
    {
        $setting = static::query()->first() ?? new static();
        $setting->fill($data);
        $setting->save();

        return $setting;
    }
}
