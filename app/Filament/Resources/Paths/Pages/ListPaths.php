<?php

namespace App\Filament\Resources\Paths\Pages;

use App\Filament\Resources\Paths\PathResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaths extends ListRecords
{
    protected static string $resource = PathResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
