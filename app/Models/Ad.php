<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'url',
        'location',
        'active',
        'clicks',
        'views',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'active' => 'boolean',
        'clicks' => 'integer',
        'views' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }
    
    public function scopeLocation($query, $location)
    {
        return $query->where('location', $location);
    }
}
