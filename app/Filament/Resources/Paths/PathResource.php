<?php

namespace App\Filament\Resources\Paths;

use App\Filament\Resources\Paths\Pages\CreatePath;
use App\Filament\Resources\Paths\Pages\EditPath;
use App\Filament\Resources\Paths\Pages\ListPaths;
use App\Filament\Resources\Paths\Schemas\PathForm;
use App\Filament\Resources\Paths\Tables\PathsTable;
use App\Models\Drug;
use App\Models\Journey;
use App\Models\Path;
use App\Settings\DogSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class PathResource extends Resource
{
    protected static ?string $model = Path::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Robot');
    }

    public static function form(Schema $schema): Schema
    {
        return PathForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PathsTable::configure($table);
    }

    public static function dispatchAction(): Action
    {
        return Action::make('dispatch')
            ->label(__('Dispatch'))
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('success')
            ->modalHeading(__('Dispatch Path'))
            ->schema([
                Repeater::make('drugs')
                    ->label(__('Drugs to carry'))
                    ->schema([
                        Select::make('drug_id')
                            ->label(__('Drug'))
                            ->options(Drug::where('is_active', true)->pluck('name', 'id'))
                            ->required(),
                        TextInput::make('quantity')
                            ->label(__('Quantity'))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->columns(2)
                    ->minItems(1),
            ])
            ->action(function (Path $record, array $data): void {
                $journey = Journey::create([
                    'path_id' => $record->id,
                    'dispatched_at' => now(),
                    'status' => 'success',
                ]);

                $drugs = collect($data['drugs'])->mapWithKeys(
                    fn (array $item): array => [$item['drug_id'] => ['quantity' => (int) $item['quantity']]]
                );
                $journey->drugs()->attach($drugs);

                $ip = app(DogSettings::class)->ip_address;

                try {
                    $response = Http::timeout(5)->get("http://{$ip}:8000/api/move/{$record->going_moves}");

                    if (! $response->successful()) {
                        $journey->update(['status' => 'error']);
                        Notification::make()
                            ->title(__('Dispatch failed'))
                            ->body(__('API returned status :status', ['status' => $response->status()]))
                            ->danger()
                            ->send();

                        return;
                    }
                } catch (\Throwable) {
                    $journey->update(['status' => 'error']);
                    Notification::make()
                        ->title(__('Dispatch failed'))
                        ->body(__('Could not reach the robot'))
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(__('Dispatched successfully'))
                    ->success()
                    ->send();
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaths::route('/'),
            'create' => CreatePath::route('/create'),
            'edit' => EditPath::route('/{record}/edit'),
        ];
    }
}
