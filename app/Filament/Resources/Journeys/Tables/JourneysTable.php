<?php

namespace App\Filament\Resources\Journeys\Tables;

use App\Models\Journey;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JourneysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['path', 'drugs']))
            ->defaultSort('dispatched_at', 'desc')
            ->columns([
                TextColumn::make('path.name')
                    ->label('Trasa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('drugs_summary')
                    ->label('Léky')
                    ->state(fn (Journey $record): string => $record->drugs
                        ->map(fn ($drug): string => "{$drug->name} (×{$drug->pivot->quantity})")
                        ->join(', ') ?: '—'
                    ),
                TextColumn::make('dispatched_at')
                    ->label('Vyslán')
                    ->dateTime('d. m. Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'success' => 'Úspěch',
                        'error' => 'Chyba',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
