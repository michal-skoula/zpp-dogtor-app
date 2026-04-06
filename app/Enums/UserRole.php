<?php

namespace App\Enums;

enum UserRole: string
{
    case Doctor = 'doctor';
    case Patient = 'patient';
}
