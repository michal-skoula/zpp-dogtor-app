<?php

namespace App\Livewire;

use App\Settings\DogSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class DogStatusIndicator extends Component
{
    public string $status = 'unknown';

    public function checkHealth(): void
    {
        $ip = app(DogSettings::class)->ip_address;

        try {
            $response = Http::timeout(2)->get("http://{$ip}:8000/api/health");

            if ($response->successful()) {
                $data = $response->json();
                $this->status = ($data['dry_run'] ?? false) ? 'dry_run' : 'ok';
            } else {
                $this->status = 'error';
            }
        } catch (\Throwable) {
            $this->status = 'error';
        }
    }

    public function render(): View
    {
        return view('livewire.dog-status-indicator');
    }
}
