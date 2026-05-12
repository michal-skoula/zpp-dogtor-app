<?php

namespace App\Filament\Resources\Paths\Pages;

use App\Filament\Resources\Paths\PathResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPath extends EditRecord
{
    protected static string $resource = PathResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
