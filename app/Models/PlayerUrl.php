<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PlayerUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'is_active',
        'status',
        'metadata',
        'last_tested_at',
        'test_result',
        'test_response_time',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'last_tested_at' => 'datetime',
        'test_response_time' => 'integer',
    ];

    // Estados disponibles
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_TESTING = 'testing';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_TESTING => 'Probando',
        ];
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeRecentlyTested(Builder $query): Builder
    {
        return $query->whereNotNull('last_tested_at')
                    ->orderBy('last_tested_at', 'desc');
    }

    // Métodos estáticos útiles
    public static function getActive(): ?self
    {
        return static::active()->first();
    }

    public static function getActiveUrl(): ?string
    {
        $activePlayerUrl = static::getActive();
        return $activePlayerUrl?->url;
    }

    public static function activateUrl(int $id): bool
    {
        // Desactivar todas las URLs
        static::query()->update(['is_active' => false, 'status' => self::STATUS_INACTIVE]);
        
        // Activar la URL seleccionada
        $playerUrl = static::find($id);
        if ($playerUrl) {
            $playerUrl->update([
                'is_active' => true,
                'status' => self::STATUS_ACTIVE
            ]);
            return true;
        }
        
        return false;
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->is_active;
    }

    public function getLastTestedHumanAttribute(): ?string
    {
        return $this->last_tested_at?->diffForHumans();
    }

    public function getTestStatusAttribute(): string
    {
        if (!$this->last_tested_at) {
            return 'Sin probar';
        }

        if (!$this->test_result) {
            return 'Test fallido';
        }

        return 'Test exitoso';
    }

    public function getFormattedResponseTimeAttribute(): string
    {
        if (!$this->test_response_time) {
            return 'N/A';
        }

        return $this->test_response_time . ' ms';
    }

    // Métodos de utilidad
    public function isActive(): bool
    {
        return $this->is_active && $this->status === self::STATUS_ACTIVE;
    }

    public function isTesting(): bool
    {
        return $this->status === self::STATUS_TESTING;
    }

    public function activate(): void
    {
        // Usar el método estático para garantizar que solo una esté activa
        static::activateUrl($this->id);
    }

    public function deactivate(): void
    {
        $this->update([
            'is_active' => false,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    public function markAsTesting(): void
    {
        $this->update([
            'status' => self::STATUS_TESTING
        ]);
    }

    public function updateTestResult(bool $success, int $responseTime = null, string $result = null): void
    {
        $this->update([
            'last_tested_at' => now(),
            'test_result' => $success ? ($result ?: 'Conexión exitosa') : ($result ?: 'Conexión fallida'),
            'test_response_time' => $responseTime,
        ]);
    }

    public function hasBeenTested(): bool
    {
        return !is_null($this->last_tested_at);
    }

    public function isRecentlyTested(int $minutes = 60): bool
    {
        if (!$this->last_tested_at) {
            return false;
        }

        return $this->last_tested_at->isAfter(now()->subMinutes($minutes));
    }

    public function needsRetesting(int $minutes = 60): bool
    {
        return !$this->isRecentlyTested($minutes);
    }
}
