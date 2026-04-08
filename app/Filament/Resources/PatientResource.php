<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers\PrescriptionsRelationManager;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'patients';

    public static function getNavigationGroup(): ?string
    {
        return __('Clinical');
    }

    public static function getNavigationLabel(): string
    {
        return __('My Patients');
    }

    public static function getModelLabel(): string
    {
        return __('Patient');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Patients');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', UserRole::Patient)
            ->whereHas('prescriptionsAsPatient', fn(Builder $q) => $q->where('doctor_id', auth()->id()));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('Patient Details'))->schema([
                TextEntry::make('name')->label('Jméno'),
                TextEntry::make('email')->label('E-mail'),
                TextEntry::make('patientProfile.date_of_birth')
                    ->date()
                    ->label(__('Date of Birth')),
                TextEntry::make('patientProfile.notes')
                    ->label(__('Notes'))
                    ->placeholder(__('None'))
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Jméno')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('patientProfile.date_of_birth')
                    ->date()
                    ->label(__('Date of Birth')),
                TextColumn::make('prescriptionsAsPatient_count')
                    ->counts('prescriptionsAsPatient')
                    ->label(__('Prescriptions')),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            PrescriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'view'  => Pages\ViewPatient::route('/{record}'),
        ];
    }
}
