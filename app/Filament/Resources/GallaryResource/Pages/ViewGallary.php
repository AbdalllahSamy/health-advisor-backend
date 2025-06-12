<?php

namespace App\Filament\Resources\GallaryResource\Pages;

use App\Filament\Resources\GallaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGallary extends ViewRecord
{
    protected static string $resource = GallaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
