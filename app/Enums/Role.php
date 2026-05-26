<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Conseiller = 'conseiller';

    public function label(): string
    {
        return match($this) {
            Role::SuperAdmin => 'Super Administrateur',
            Role::Conseiller => 'Conseiller',
        };
    }
}
