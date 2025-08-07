<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RssFeed extends Model
{
    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FAILED = 'failed';
    public const STATUS_TESTING = 'testing';

    protected $fillable = [
        'category_id',
        'name',
        'url',
        'is_active',
        'priority',
        'last_fetched_at',
        'status',
        'success_count',
        'error_count',
        'last_error',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_fetched_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Relación con Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación con Articles importados
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('name');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Accessors
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => match ($attributes['status']) {
                self::STATUS_ACTIVE => 'Activo',
                self::STATUS_FAILED => 'Error',
                self::STATUS_TESTING => 'Probando',
                default => 'Desconocido',
            },
        );
    }

    protected function lastFetchedHuman(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->last_fetched_at?->diffForHumans() ?? 'Nunca',
        );
    }

    protected function successRate(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = $this->success_count + $this->error_count;
                if ($total === 0) {
                    return 0;
                }
                return round(($this->success_count / $total) * 100, 1);
            },
        );
    }

    /**
     * Métodos de utilidad
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_FAILED => 'Error',
            self::STATUS_TESTING => 'Probando',
        ];
    }

    public function markAsActive(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'last_error' => null,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'last_error' => $error,
            'error_count' => $this->error_count + 1,
        ]);
    }

    public function markAsTesting(): void
    {
        $this->update([
            'status' => self::STATUS_TESTING,
            'last_error' => null,
        ]);
    }

    public function incrementSuccess(): void
    {
        $this->increment('success_count');
        $this->update([
            'last_fetched_at' => now(),
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public function isRecentlyFetched(int $minutes = 60): bool
    {
        return $this->last_fetched_at && $this->last_fetched_at->gt(now()->subMinutes($minutes));
    }

    /**
     * Get feeds ready for import (active and not recently fetched)
     */
    public static function readyForImport(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->byPriority()
            ->where(function ($query) {
                $query->whereNull('last_fetched_at')
                      ->orWhere('last_fetched_at', '<=', now()->subHour());
            })
            ->get();
    }
}
