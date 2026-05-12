<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DogSettings extends Settings
{
    public string $ip_address;

    public static function group(): string
    {
        return 'dog';
    }
}
