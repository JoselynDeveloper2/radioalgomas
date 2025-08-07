<?php

namespace App\Filament\Resources\PlayerUrlResource\Pages;

use App\Filament\Resources\PlayerUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlayerUrl extends EditRecord
{
    protected static string $resource = PlayerUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
