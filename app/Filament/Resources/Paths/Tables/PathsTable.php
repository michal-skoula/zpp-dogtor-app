<?php

namespace App\Filament\Resources\Paths\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PathsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Popis')
                    ->limit(60)
                    ->placeholder('—'),
                TextColumn::make('going_moves')
                    ->label('Cesta tam')
                    ->limit(40),
                TextColumn::make('updated_at')
                    ->label('Aktualizováno')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()->label('Upravit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Smazat vybrané'),
                ]),
            ]);
    }
}
