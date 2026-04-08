<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\PrescriptionResource\Pages;
use App\Models\Prescription;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Clinical');
    }

    public static function getModelLabel(): string
    {
        return __('Prescription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Prescriptions');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('doctor_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        $isCreate = $schema->getOperation() === 'create';

        $step1 = [
            Select::make('patient_id')
                ->label(__('Patient'))
                ->relationship('patient', 'name', fn(Builder $query) => $query->where('role', UserRole::Patient))
                ->searchable()
                ->preload()
                ->required(),
            Select::make('drug_id')
                ->label(__('Drug'))
                ->relationship('drug', 'name', fn(Builder $query) => $query->where('is_active', true))
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    TextInput::make('name')->label('Název')->required()->maxLength(100),
                    TextInput::make('strength')->label('Síla')->maxLength(50),
                    Toggle::make('is_active')->label(__('Active'))->default(true),
                ]),
        ];

        $step2 = [
            Grid::make(2)->schema([
                TextInput::make('dose_amount')
                    ->label('Množství dávky')
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->required(),
                Select::make('dose_unit')
                    ->label('Jednotka dávky')
                    ->options(['mg' => 'mg', 'ml' => 'ml', 'mcg' => 'mcg', 'IU' => 'IU', 'tablet' => 'tablet', 'capsule' => 'capsule'])
                    ->required(),
            ]),
            Grid::make(3)->schema([
                TextInput::make('frequency_value')
                    ->label(__('Every'))
                    ->integer()
                    ->minValue(1)
                    ->required(),
                Select::make('frequency_unit')
                    ->label(__('Unit'))
                    ->options([
                        'hour' => __('hour(s)'),
                        'day'  => __('day(s)'),
                        'week' => __('week(s)'),
                    ])
                    ->required(),
                TextInput::make('times_per_dose')
                    ->label(__('Times per dose'))
                    ->integer()
                    ->minValue(1)
                    ->default(1)
                    ->required(),
            ]),
            Textarea::make('instructions')
                ->label(__('Instructions'))
                ->rows(3)
                ->columnSpanFull(),
        ];

        $step3 = [
            DatePicker::make('starts_on')
                ->label('Začátek')
                ->required()
                ->default(today()),
            DatePicker::make('ends_on')
                ->label('Konec')
                ->nullable()
                ->afterOrEqual('starts_on'),
            Toggle::make('is_active')
                ->label(__('Active'))
                ->default(true)
                ->inline(false),
            Repeater::make('schedules')
                ->relationship('schedules')
                ->schema([
                    TimePicker::make('time_of_day')
                        ->label('Čas dávky')
                        ->seconds(false)
                        ->required(),
                    TextInput::make('label')
                        ->label('Popis')
                        ->maxLength(50)
                        ->placeholder(__('Morning, After lunch…')),
                ])
                ->columns(2)
                ->addActionLabel(__('Add dose time'))
                ->defaultItems(0)
                ->collapsible()
                ->columnSpanFull(),
        ];

        if ($isCreate) {
            return $schema->schema([
                Wizard::make([
                    Wizard\Step::make(__('Patient & Drug'))->schema($step1),
                    Wizard\Step::make(__('Dosage & Frequency'))->schema($step2),
                    Wizard\Step::make(__('Schedule & Dates'))->schema($step3),
                ])->columnSpanFull(),
            ]);
        }

        return $schema->schema([
            Section::make(__('Patient & Drug'))->schema($step1),
            Section::make(__('Dosage & Frequency'))->schema($step2),
            Section::make(__('Schedule & Dates'))->schema($step3),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('Patient & Drug'))->schema([
                TextEntry::make('patient.name')->label(__('Patient')),
                TextEntry::make('drug.name')->label(__('Drug')),
                TextEntry::make('drug.strength')->label(__('Strength')),
            ])->columns(3),
            Section::make(__('Dosage'))->schema([
                TextEntry::make('dose_amount')->label(__('Dose Amount')),
                TextEntry::make('dose_unit')->label(__('Unit')),
                TextEntry::make('frequency_value')->label(__('Every')),
                TextEntry::make('frequency_unit')->label(__('Frequency Unit')),
                TextEntry::make('times_per_dose')->label(__('Times per Dose')),
                TextEntry::make('instructions')->label(__('Instructions'))->placeholder(__('None'))->columnSpanFull(),
            ])->columns(3),
            Section::make(__('Schedule'))->schema([
                TextEntry::make('starts_on')->date()->label(__('Starts')),
                TextEntry::make('ends_on')->date()->label(__('Ends'))->placeholder(__('Ongoing')),
                IconEntry::make('is_active')->boolean()->label(__('Active')),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.name')
                    ->label(__('Patient'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('drug.name')
                    ->label(__('Drug'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('dose_amount')
                    ->label(__('Dose'))
                    ->formatStateUsing(fn($record) => $record->dose_amount . ' ' . $record->dose_unit),
                TextColumn::make('frequency_value')
                    ->label(__('Frequency'))
                    ->formatStateUsing(fn($record) => __('Every') . ' ' . $record->frequency_value . ' ' . $record->frequency_unit . ', ' . $record->times_per_dose . 'x'),
                TextColumn::make('starts_on')
                    ->label('Začátek')
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_on')
                    ->date()
                    ->placeholder(__('Ongoing')),
                ToggleColumn::make('is_active')
                    ->label(__('Active')),
            ])
            ->filters([
                SelectFilter::make('patient_id')
                    ->relationship('patient', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('Patient')),
                SelectFilter::make('drug_id')
                    ->relationship('drug', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('Drug')),
                TernaryFilter::make('is_active')->label(__('Active only')),
                Filter::make('active_today')
                    ->label(__('Active Today'))
                    ->query(fn(Builder $query) => $query
                        ->where('starts_on', '<=', today())
                        ->where(fn(Builder $q) => $q
                            ->whereNull('ends_on')
                            ->orWhere('ends_on', '>=', today())
                        )
                    ),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'edit'   => Pages\EditPrescription::route('/{record}/edit'),
            'view'   => Pages\ViewPrescription::route('/{record}'),
        ];
    }
}
