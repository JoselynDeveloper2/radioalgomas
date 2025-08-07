<?php

namespace App\Filament\Resources\PlayerUrlResource\Pages;

use App\Filament\Resources\PlayerUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlayerUrls extends ListRecords
{
    protected static string $resource = PlayerUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
