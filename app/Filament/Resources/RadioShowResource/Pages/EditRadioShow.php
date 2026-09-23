<?php

namespace App\Filament\Resources\RadioShowResource\Pages;

use App\Filament\Resources\RadioShowResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRadioShow extends EditRecord
{
    protected static string $resource = RadioShowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
