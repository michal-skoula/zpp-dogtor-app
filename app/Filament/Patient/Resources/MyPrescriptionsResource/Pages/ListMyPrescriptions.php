<?php

namespace App\Filament\Patient\Resources\MyPrescriptionsResource\Pages;

use App\Filament\Patient\Resources\MyPrescriptionsResource;
use Filament\Resources\Pages\ListRecords;

class ListMyPrescriptions extends ListRecords
{
    protected static string $resource = MyPrescriptionsResource::class;
}
