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

    protected static string|\UnitEnum|null $navigationGroup = 'Clinical';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'My Patients';

    protected static ?string $modelLabel = 'Patient';

    protected static ?string $pluralModelLabel = 'Patients';

    protected static ?string $slug = 'patients';

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
            Section::make('Patient Details')->schema([
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('patientProfile.date_of_birth')
                    ->date()
                    ->label('Date of Birth'),
                TextEntry::make('patientProfile.notes')
                    ->label('Notes')
                    ->placeholder('None')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('patientProfile.date_of_birth')
                    ->date()
                    ->label('Date of Birth'),
                TextColumn::make('prescriptionsAsPatient_count')
                    ->counts('prescriptionsAsPatient')
                    ->label('Prescriptions'),
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
