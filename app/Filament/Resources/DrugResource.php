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

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Catalog');
    }

    public static function getModelLabel(): string
    {
        return __('Drug');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Drugs');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('Drug Identity'))->schema([
                TextInput::make('name')
                    ->label('Název')
                    ->required()
                    ->unique(Drug::class, 'name', ignoreRecord: true)
                    ->maxLength(100),
                TextInput::make('generic_name')
                    ->label('Generický název')
                    ->maxLength(100),
                Grid::make(2)->schema([
                    TextInput::make('form')
                        ->label('Forma')
                        ->maxLength(50)
                        ->placeholder(__('tablet, syrup, injection…')),
                    TextInput::make('strength')
                        ->label('Síla')
                        ->maxLength(50)
                        ->placeholder('400mg, 250mg/5ml…'),
                ]),
            ]),
            Section::make(__('Details'))->schema([
                Textarea::make('description')
                    ->label('Popis')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label(__('Active'))
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
                    ->label('Název')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('generic_name')
                    ->label('Generický název')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('form')
                    ->label('Forma')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('strength')
                    ->label('Síla'),
                ToggleColumn::make('is_active')
                    ->label(__('Active')),
                TextColumn::make('updated_at')
                    ->label('Aktualizováno')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label(__('Active only')),
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
