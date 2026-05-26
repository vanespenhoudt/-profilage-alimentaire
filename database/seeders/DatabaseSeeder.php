<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@profilage.local',
            'password' => Hash::make('Admin2024!'),
            'role'     => Role::SuperAdmin->value,
            'active'   => true,
        ]);

        // Conseillers de test
        $conseiller1 = User::create([
            'name'     => 'Marie Dupont',
            'email'    => 'conseiller1@profilage.local',
            'password' => Hash::make('Conseil2024!'),
            'role'     => Role::Conseiller->value,
            'code'     => 'CONS-001',
            'active'   => true,
        ]);

        $conseiller2 = User::create([
            'name'     => 'Pierre Martin',
            'email'    => 'conseiller2@profilage.local',
            'password' => Hash::make('Conseil2024!'),
            'role'     => Role::Conseiller->value,
            'code'     => 'CONS-002',
            'active'   => true,
        ]);

        // Clients de test répartis entre les conseillers
        Client::create([
            'conseiller_id' => $conseiller1->id,
            'prenom'        => 'Alice',
            'nom'           => 'Bernard',
            'tel'           => '+32 475 12 34 56',
            'email'         => 'alice.bernard@exemple.com',
            'adresse'       => 'Rue de la Paix 12, 1000 Bruxelles',
            'bt'            => 'Bilan initial réalisé le 10/01/2026. Patient en bonne santé générale.',
            'rgpd'          => true,
            'notes'         => 'Cliente très motivée.',
        ]);

        Client::create([
            'conseiller_id' => $conseiller1->id,
            'prenom'        => 'Jean-Pierre',
            'nom'           => 'Lecomte',
            'tel'           => '+32 498 65 43 21',
            'email'         => null,
            'adresse'       => 'Avenue Louise 50, 1050 Ixelles',
            'bt'            => 'Surpoids modéré. Suivi nutritionnel recommandé.',
            'rgpd'          => true,
            'notes'         => null,
        ]);

        Client::create([
            'conseiller_id' => $conseiller1->id,
            'prenom'        => 'Sophie',
            'nom'           => 'Noel',
            'tel'           => '+32 470 00 11 22',
            'email'         => 'sophie.noel@mail.be',
            'adresse'       => null,
            'bt'            => null,
            'rgpd'          => true,
            'notes'         => 'Rendez-vous prévu fin janvier.',
        ]);

        Client::create([
            'conseiller_id' => $conseiller2->id,
            'prenom'        => 'Thomas',
            'nom'           => 'Renard',
            'tel'           => '+32 487 33 44 55',
            'email'         => 'thomas.renard@exemple.com',
            'adresse'       => 'Rue du Marché 7, 4000 Liège',
            'bt'            => 'Déficiences identifiées en vitamines D et B12.',
            'rgpd'          => true,
            'notes'         => 'Suivi mensuel.',
        ]);

        Client::create([
            'conseiller_id' => $conseiller2->id,
            'prenom'        => 'Isabelle',
            'nom'           => 'Claes',
            'tel'           => '+32 468 77 88 99',
            'email'         => 'i.claes@exemple.be',
            'adresse'       => 'Chaussée de Namur 100, 5000 Namur',
            'bt'            => 'Profil alimentaire déséquilibré. Programme détox recommandé.',
            'rgpd'          => true,
            'notes'         => 'Préfère les rendez-vous le matin.',
        ]);
    }
}
