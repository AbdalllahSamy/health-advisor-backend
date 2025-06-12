<?php

namespace App\Filament\Resources\GallaryResource\Pages;

use App\Filament\Resources\GallaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGallaries extends ListRecords
{
    protected static string $resource = GallaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
