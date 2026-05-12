<?php

namespace App\Filament\Resources\Journeys;

use App\Filament\Resources\Journeys\Pages\ListJourneys;
use App\Filament\Resources\Journeys\Tables\JourneysTable;
use App\Models\Journey;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JourneyResource extends Resource
{
    protected static ?string $model = Journey::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Robot';
    }

    public static function getModelLabel(): string
    {
        return 'Cesta';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Cesty';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return JourneysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJourneys::route('/'),
        ];
    }
}
