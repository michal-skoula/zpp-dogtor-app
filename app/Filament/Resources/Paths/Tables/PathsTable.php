<?php

namespace App\Filament\Resources\Paths\Tables;

use App\Filament\Resources\Paths\PathResource;
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
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(60)
                    ->placeholder('—'),
                TextColumn::make('going_moves')
                    ->label(__('Going Moves'))
                    ->limit(40),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                PathResource::dispatchAction(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
