<?php

namespace App\Filament\Patient\Resources;

use App\Filament\Patient\Resources\MyPrescriptionsResource\Pages;
use App\Models\Prescription;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyPrescriptionsResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function getModelLabel(): string
    {
        return __('Prescription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('My Prescriptions');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('patient_id', auth()->id());
    }

    public static function canCreate(): bool { return false; }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('Drug'))->schema([
                TextEntry::make('drug.name')->label(__('Drug')),
                TextEntry::make('drug.strength')->label(__('Strength'))->placeholder('—'),
                TextEntry::make('drug.form')->label(__('Form'))->placeholder('—'),
            ])->columns(2),

            Section::make(__('Dosage'))->schema([
                TextEntry::make('dose_amount')
                    ->label(__('Dose'))
                    ->formatStateUsing(fn($record) => $record->dose_amount . ' ' . $record->dose_unit),
                TextEntry::make('frequency_value')
                    ->label(__('Frequency'))
                    ->formatStateUsing(fn($record) => __('Every') . ' ' . $record->frequency_value . ' ' . __($record->frequency_unit . '(s)') . ', ' . $record->times_per_dose . 'x per dose'),
                TextEntry::make('instructions')
                    ->label(__('Instructions'))
                    ->placeholder(__('None'))
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make(__('Schedule'))->schema([
                TextEntry::make('starts_on')->date()->label(__('Starts')),
                TextEntry::make('ends_on')->date()->label(__('Ends'))->placeholder(__('Ongoing')),
                IconEntry::make('is_active')->boolean()->label(__('Active')),
                RepeatableEntry::make('schedules')
                    ->label(__('Dose Times'))
                    ->schema([
                        TextEntry::make('time_of_day')
                            ->label(__('Time'))
                            ->time('H:i'),
                        TextEntry::make('label')
                            ->label(__('Label'))
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])->columns(3),

            Section::make(__('Prescriber'))->schema([
                TextEntry::make('doctor.name')->label(__('Doctor')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('drug.name')
                    ->label(__('Drug'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('drug.strength')
                    ->label(__('Strength')),
                TextColumn::make('dose_amount')
                    ->label(__('Dose'))
                    ->formatStateUsing(fn($record) => $record->dose_amount . ' ' . $record->dose_unit),
                TextColumn::make('frequency_value')
                    ->label(__('Frequency'))
                    ->formatStateUsing(fn($record) => __('Every') . ' ' . $record->frequency_value . ' ' . __($record->frequency_unit . '(s)') . ''),
                TextColumn::make('starts_on')->label(__('Starts'))->date()->sortable(),
                TextColumn::make('ends_on')->label(__('Ends'))->date()->placeholder(__('Ongoing')),
                IconColumn::make('is_active')->boolean()->label(__('Active')),
            ])
            ->defaultSort('is_active', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyPrescriptions::route('/'),
            'view'  => Pages\ViewMyPrescription::route('/{record}'),
        ];
    }
}
