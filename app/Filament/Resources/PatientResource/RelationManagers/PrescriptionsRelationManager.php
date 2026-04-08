<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use App\Filament\Resources\PrescriptionResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrescriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'prescriptionsAsPatient';

    public function canCreate(): bool { return false; }
    public function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return false; }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('doctor_id', auth()->id()))
            ->columns([
                TextColumn::make('drug.name')->label(__('Drug'))->sortable(),
                TextColumn::make('dose_amount')
                    ->label(__('Dose'))
                    ->formatStateUsing(fn($record) => $record->dose_amount . ' ' . $record->dose_unit),
                TextColumn::make('frequency_value')
                    ->label(__('Frequency'))
                    ->formatStateUsing(fn($record) => __('Every') . ' ' . $record->frequency_value . ' ' . $record->frequency_unit . ', ' . $record->times_per_dose . 'x'),
                TextColumn::make('starts_on')->date()->label('Začátek'),
                TextColumn::make('ends_on')->date()->placeholder(__('Ongoing')),
                IconColumn::make('is_active')->boolean()->label(__('Active')),
            ])
            ->headerActions([])
            ->actions([
                ViewAction::make()
                    ->url(fn($record) => PrescriptionResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
