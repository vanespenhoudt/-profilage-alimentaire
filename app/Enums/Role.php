<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Conseiller = 'conseiller';
    case Client     = 'client';

    public function label(): string
    {
        return match($this) {
            Role::SuperAdmin => 'Super Administrateur',
            Role::Conseiller => 'Conseiller',
            Role::Client     => 'Client',
        };
    }
}
