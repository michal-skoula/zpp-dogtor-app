<?php

namespace App\Filament\Resources\Paths\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PathForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->schema([
                        TextInput::make('name')
                            ->label('Název')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Popis')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Instrukce pohybu')
                    ->description('Řetězce pohybů předávané na /api/move/{moves}')
                    ->schema([
                        TextInput::make('going_moves')
                            ->label('Cesta tam')
                            ->required()
                            ->placeholder('forward:1,left:1'),
                        TextInput::make('button_press_moves')
                            ->label('Po stisku tlačítka')
                            ->required()
                            ->placeholder('sit:1'),
                        TextInput::make('return_moves')
                            ->label('Cesta zpět')
                            ->required()
                            ->placeholder('backward:1'),
                    ]),
            ]);
    }
}
