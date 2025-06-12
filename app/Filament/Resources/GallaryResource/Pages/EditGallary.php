<?php

namespace App\Filament\Resources\GallaryResource\Pages;

use App\Filament\Resources\GallaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGallary extends EditRecord
{
    protected static string $resource = GallaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
