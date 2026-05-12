<?php

namespace App\Filament\Pages;

use App\Settings\DogSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageDogSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'Robot Settings';

    protected static ?string $title = 'Robot Settings';

    protected static string $settings = DogSettings::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ip_address')
                    ->label('Dog IP Address')
                    ->required(),
            ]);
    }
}
