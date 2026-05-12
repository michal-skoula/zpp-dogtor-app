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
                Section::make(__('Path Identity'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Move Instructions'))
                    ->description(__('Comma-separated move strings passed to /api/move/{moves}'))
                    ->schema([
                        TextInput::make('going_moves')
                            ->label(__('Going Moves'))
                            ->required()
                            ->placeholder('forward,left,forward'),
                        TextInput::make('button_press_moves')
                            ->label(__('Button Press Moves'))
                            ->required()
                            ->placeholder('sit,wait'),
                        TextInput::make('return_moves')
                            ->label(__('Return Moves'))
                            ->required()
                            ->placeholder('right,right,forward'),
                    ]),
            ]);
    }
}
