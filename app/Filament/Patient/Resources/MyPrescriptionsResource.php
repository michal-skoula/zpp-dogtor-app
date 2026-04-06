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

    protected static ?string $modelLabel = 'Prescription';

    protected static ?string $pluralModelLabel = 'My Prescriptions';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('patient_id', auth()->id());
    }

    public static function canCreate(): bool { return false; }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Drug')->schema([
                TextEntry::make('drug.name')->label('Drug'),
                TextEntry::make('drug.generic_name')->label('Generic Name')->placeholder('—'),
                TextEntry::make('drug.strength')->label('Strength')->placeholder('—'),
                TextEntry::make('drug.form')->label('Form')->placeholder('—'),
            ])->columns(2),

            Section::make('Dosage')->schema([
                TextEntry::make('dose_amount')
                    ->label('Dose')
                    ->formatStateUsing(fn($record) => $record->dose_amount . ' ' . $record->dose_unit),
                TextEntry::make('frequency_value')
                    ->label('Frequency')
                    ->formatStateUsing(fn($record) => 'Every ' . $record->frequency_value . ' ' . $record->frequency_unit . '(s), ' . $record->times_per_dose . 'x per dose'),
                TextEntry::make('instructions')
                    ->label('Instructions')
                    ->placeholder('None')
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Schedule')->schema([
                TextEntry::make('starts_on')->date()->label('Starts'),
                TextEntry::make('ends_on')->date()->label('Ends')->placeholder('Ongoing'),
                IconEntry::make('is_active')->boolean()->label('Active'),
                RepeatableEntry::make('schedules')
                    ->label('Dose Times')
                    ->schema([
                        TextEntry::make('time_of_day')
                            ->label('Time')
                            ->time('H:i'),
                        TextEntry::make('label')
                            ->label('Label')
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])->columns(3),

            Section::make('Prescriber')->schema([
                TextEntry::make('doctor.name')->label('Doctor'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('drug.name')
                    ->label('Drug')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('drug.strength')
                    ->label('Strength'),
                TextColumn::make('dose_amount')
                    ->label('Dose')
                    ->formatStateUsing(fn($record) => $record->dose_amount . ' ' . $record->dose_unit),
                TextColumn::make('frequency_value')
                    ->label('Frequency')
                    ->formatStateUsing(fn($record) => 'Every ' . $record->frequency_value . ' ' . $record->frequency_unit . '(s)'),
                TextColumn::make('starts_on')->date()->sortable(),
                TextColumn::make('ends_on')->date()->placeholder('Ongoing'),
                IconColumn::make('is_active')->boolean()->label('Active'),
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
