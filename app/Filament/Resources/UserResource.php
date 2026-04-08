<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function form(Schema $schema): Schema
    {
        $isCreate = $schema->getOperation() === 'create';

        $accountFields = [
            TextInput::make('name')
                ->label('Jméno')
                ->required()
                ->maxLength(100),
            TextInput::make('email')
                ->label('E-mail')
                ->email()
                ->required()
                ->unique(User::class, 'email', ignoreRecord: true)
                ->maxLength(255),
            TextInput::make('password')
                ->label('Heslo')
                ->password()
                ->revealable()
                ->required(fn(string $operation) => $operation === 'create')
                ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn($state) => filled($state))
                ->maxLength(255),
            Select::make('role')
                ->label('Role')
                ->options(UserRole::class)
                ->required()
                ->live(),
        ];

        $profileFields = [
            Section::make(__('Doctor Profile'))
                ->relationship('doctorProfile')
                ->schema([
                    TextInput::make('specialty')->label('Specializace')->maxLength(100),
                    TextInput::make('license_number')->label('Číslo licence')->maxLength(50),
                ])
                ->visible(fn(Get $get) => $get('role') === UserRole::Doctor->value),

            Section::make(__('Patient Profile'))
                ->relationship('patientProfile')
                ->schema([
                    DatePicker::make('date_of_birth')->label('Datum narození')->maxDate(now()),
                    Textarea::make('notes')->label('Poznámky')->rows(4)->columnSpanFull(),
                ])
                ->visible(fn(Get $get) => $get('role') === UserRole::Patient->value),
        ];

        if ($isCreate) {
            return $schema->schema([
                Wizard::make([
                    Wizard\Step::make(__('Account'))->schema($accountFields),
                    Wizard\Step::make(__('Profile'))->schema($profileFields),
                ])->columnSpanFull(),
            ]);
        }

        return $schema->schema([
            Section::make(__('Account'))->schema($accountFields),
            ...$profileFields,
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
                    ->searchable()
                    ->copyable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn($state) => $state === UserRole::Doctor ? 'success' : 'info'),
                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(UserRole::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
