<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = [
        'name',
        'stream_url',
        'is_active',
        'backup_url',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the active stream URL
     * Updated to use the new PlayerUrl system
     */
    public static function getActiveStreamUrl(): string
    {
        // First, try to get from new PlayerUrl system
        $activePlayerUrl = \App\Models\PlayerUrl::getActiveUrl();
        if ($activePlayerUrl) {
            return $activePlayerUrl;
        }
        
        // Fallback to old system
        $activePlayer = static::where('is_active', true)->first();
        
        return $activePlayer 
            ? $activePlayer->stream_url 
            : 'https://ott1.tucanaltv.tv/live_abr/tucanaltv/playlist.m3u8';
    }

    /**
     * Scope to get only active players
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
