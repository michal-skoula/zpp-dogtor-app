<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DrugResource\Pages;
use App\Models\Drug;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DrugResource extends Resource
{
    protected static ?string $model = Drug::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Drug Identity')->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(Drug::class, 'name', ignoreRecord: true)
                    ->maxLength(100),
                TextInput::make('generic_name')
                    ->maxLength(100),
                Grid::make(2)->schema([
                    TextInput::make('form')
                        ->maxLength(50)
                        ->placeholder('tablet, syrup, injection…'),
                    TextInput::make('strength')
                        ->maxLength(50)
                        ->placeholder('400mg, 250mg/5ml…'),
                ]),
            ]),
            Section::make('Details')->schema([
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true)
                    ->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('generic_name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('form')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('strength'),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active only'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDrugs::route('/'),
            'create' => Pages\CreateDrug::route('/create'),
            'edit'   => Pages\EditDrug::route('/{record}/edit'),
        ];
    }
}
