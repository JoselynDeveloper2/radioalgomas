<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PlayerUrl;
use Illuminate\Support\Facades\Log;

class PlayerUrlObserver
{
    /**
     * Handle the PlayerUrl "creating" event.
     */
    public function creating(PlayerUrl $playerUrl): void
    {
        // Si se está creando como activa, desactivar las demás
        if ($playerUrl->is_active) {
            $this->ensureOnlyOneActive($playerUrl);
        }
        
        // Validar URL
        $this->validateUrl($playerUrl);
    }

    /**
     * Handle the PlayerUrl "updating" event.
     */
    public function updating(PlayerUrl $playerUrl): void
    {
        // Si se está activando, desactivar las demás
        if ($playerUrl->isDirty('is_active') && $playerUrl->is_active) {
            $this->ensureOnlyOneActive($playerUrl);
        }
        
        // Si se está cambiando a estado activo, también debe estar marcada como is_active
        if ($playerUrl->isDirty('status') && $playerUrl->status === PlayerUrl::STATUS_ACTIVE) {
            $playerUrl->is_active = true;
            $this->ensureOnlyOneActive($playerUrl);
        }
        
        // Si se está desactivando (is_active = false), cambiar estado a inactive
        if ($playerUrl->isDirty('is_active') && !$playerUrl->is_active) {
            $playerUrl->status = PlayerUrl::STATUS_INACTIVE;
        }
        
        // Validar URL si ha cambiado
        if ($playerUrl->isDirty('url')) {
            $this->validateUrl($playerUrl);
        }
    }

    /**
     * Handle the PlayerUrl "created" event.
     */
    public function created(PlayerUrl $playerUrl): void
    {
        Log::info("Nueva PlayerUrl creada: {$playerUrl->name}", [
            'id' => $playerUrl->id,
            'url' => $playerUrl->url,
            'is_active' => $playerUrl->is_active,
            'status' => $playerUrl->status,
        ]);
    }

    /**
     * Handle the PlayerUrl "updated" event.
     */
    public function updated(PlayerUrl $playerUrl): void
    {
        // Log cambios importantes
        if ($playerUrl->wasChanged('is_active') || $playerUrl->wasChanged('status')) {
            Log::info("PlayerUrl actualizada: {$playerUrl->name}", [
                'id' => $playerUrl->id,
                'is_active' => $playerUrl->is_active,
                'status' => $playerUrl->status,
                'changes' => $playerUrl->getChanges(),
            ]);
        }
    }

    /**
     * Handle the PlayerUrl "deleted" event.
     */
    public function deleted(PlayerUrl $playerUrl): void
    {
        Log::info("PlayerUrl eliminada: {$playerUrl->name}", [
            'id' => $playerUrl->id,
            'was_active' => $playerUrl->is_active,
        ]);
        
        // Si se eliminó la URL activa, logs para advertir
        if ($playerUrl->is_active) {
            Log::warning("Se eliminó la PlayerUrl activa. No hay URL activa ahora.");
        }
    }

    /**
     * Garantiza que solo una PlayerUrl esté activa
     */
    private function ensureOnlyOneActive(PlayerUrl $playerUrl): void
    {
        PlayerUrl::where('id', '!=', $playerUrl->id ?? 0)
                 ->where('is_active', true)
                 ->update([
                     'is_active' => false,
                     'status' => PlayerUrl::STATUS_INACTIVE
                 ]);
                 
        Log::info("Desactivadas otras PlayerUrls para activar: {$playerUrl->name}");
    }

    /**
     * Valida que la URL sea válida
     */
    private function validateUrl(PlayerUrl $playerUrl): void
    {
        if (!filter_var($playerUrl->url, FILTER_VALIDATE_URL)) {
            Log::warning("URL inválida detectada: {$playerUrl->url}");
            // No lanzar excepción aquí para permitir que Filament maneje la validación
        }
    }
}
