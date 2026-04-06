<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->getRecord();

        match ($user->role) {
            UserRole::Doctor  => $user->doctorProfile()->firstOrCreate([]),
            UserRole::Patient => $user->patientProfile()->firstOrCreate([]),
        };
    }
}
