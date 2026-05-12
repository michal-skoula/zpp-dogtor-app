<?php

namespace App\Filament\Resources\Journeys\Pages;

use App\Filament\Resources\Journeys\JourneyResource;
use App\Models\Drug;
use App\Models\Journey;
use App\Models\Path;
use App\Settings\DogSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;

class ListJourneys extends ListRecords
{
    protected static string $resource = JourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dispatch')
                ->label('Nový výjezd')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('success')
                ->modalHeading('Vyslat psa')
                ->schema([
                    Select::make('path_id')
                        ->label('Trasa')
                        ->options(Path::orderBy('name')->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Repeater::make('drugs')
                        ->label('Léky k doručení')
                        ->schema([
                            Select::make('drug_id')
                                ->label('Lék')
                                ->options(Drug::where('is_active', true)->pluck('name', 'id'))
                                ->required(),
                            TextInput::make('quantity')
                                ->label('Množství')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->required(),
                        ])
                        ->columns(2)
                        ->minItems(1),
                ])
                ->action(function (array $data): void {
                    $path = Path::findOrFail($data['path_id']);

                    $journey = Journey::create([
                        'path_id' => $path->id,
                        'dispatched_at' => now(),
                        'status' => 'success',
                    ]);

                    $drugs = collect($data['drugs'])->mapWithKeys(
                        fn (array $item): array => [$item['drug_id'] => ['quantity' => (int) $item['quantity']]]
                    );
                    $journey->drugs()->attach($drugs);

                    $ip = app(DogSettings::class)->ip_address;

                    try {
                        $response = Http::timeout(5)->post("http://{$ip}:8000/api/move/{$path->going_moves}");

                        if (! $response->successful()) {
                            $journey->update(['status' => 'error']);
                            Notification::make()
                                ->title('Vyslání selhalo')
                                ->body("API vrátilo status {$response->status()}")
                                ->danger()
                                ->send();

                            return;
                        }
                    } catch (\Throwable) {
                        $journey->update(['status' => 'error']);
                        Notification::make()
                            ->title('Vyslání selhalo')
                            ->body('Nepodařilo se spojit s robotem')
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Trasa úspěšně vyslána')
                        ->success()
                        ->send();
                }),
        ];
    }
}
