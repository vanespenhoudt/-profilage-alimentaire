<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Invitation;
use App\Models\Questionnaire;
use App\Models\User;
use App\Services\QuestionnaireScorer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeder dédié aux tests Dusk.
 *
 * Structure créée :
 *   - 1 super_admin
 *   - 2 conseillers (conseiller1 et conseiller2)
 *   - 3 clients par conseiller (6 au total)
 *   - 1 questionnaire soumis (avec scores complets) pour chaque client
 *   - 1 invitation en attente pour le super_admin
 *
 * Identifiants :
 *   super_admin  → admin@dusk.test         / Password1234!
 *   conseiller1  → conseiller1@dusk.test   / Password1234!
 *   conseiller2  → conseiller2@dusk.test   / Password1234!
 */
class DuskTestSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ──────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Admin Dusk',
            'email'    => 'admin@dusk.test',
            'password' => Hash::make('Password1234!'),
            'role'     => Role::SuperAdmin->value,
            'active'   => true,
        ]);

        // ── Conseiller 1 ─────────────────────────────────────────────────────
        $c1 = User::create([
            'name'     => 'Marie Dupont',
            'email'    => 'conseiller1@dusk.test',
            'password' => Hash::make('Password1234!'),
            'role'     => Role::Conseiller->value,
            'code'     => 'DUSK-001',
            'active'   => true,
        ]);

        // ── Conseiller 2 ─────────────────────────────────────────────────────
        $c2 = User::create([
            'name'     => 'Pierre Martin',
            'email'    => 'conseiller2@dusk.test',
            'password' => Hash::make('Password1234!'),
            'role'     => Role::Conseiller->value,
            'code'     => 'DUSK-002',
            'active'   => true,
        ]);

        // ── 3 clients pour conseiller1 ────────────────────────────────────────
        $clients1 = collect([
            ['prenom' => 'Alice',    'nom' => 'Bernard',  'email' => 'alice.bernard@dusk.test'],
            ['prenom' => 'Jean',     'nom' => 'Lecomte',  'email' => 'jean.lecomte@dusk.test'],
            ['prenom' => 'Sophie',   'nom' => 'Noel',     'email' => 'sophie.noel@dusk.test'],
        ])->map(fn ($data) => Client::create([
            'conseiller_id' => $c1->id,
            'prenom'        => $data['prenom'],
            'nom'           => $data['nom'],
            'email'         => $data['email'],
            'tel'           => '+32 475 00 00 01',
            'adresse'       => 'Rue Test 1, 1000 Bruxelles',
            'rgpd'          => true,
        ]));

        // ── 3 clients pour conseiller2 ────────────────────────────────────────
        $clients2 = collect([
            ['prenom' => 'Thomas',   'nom' => 'Renard',   'email' => 'thomas.renard@dusk.test'],
            ['prenom' => 'Isabelle', 'nom' => 'Claes',    'email' => 'isabelle.claes@dusk.test'],
            ['prenom' => 'Marc',     'nom' => 'Dubois',   'email' => 'marc.dubois@dusk.test'],
        ])->map(fn ($data) => Client::create([
            'conseiller_id' => $c2->id,
            'prenom'        => $data['prenom'],
            'nom'           => $data['nom'],
            'email'         => $data['email'],
            'tel'           => '+32 475 00 00 02',
            'adresse'       => 'Rue Test 2, 4000 Liège',
            'rgpd'          => true,
        ]));

        // ── 1 questionnaire soumis par client ────────────────────────────────
        $allClients = $clients1->concat($clients2);
        $answers    = $this->buildAnswers();
        $scores     = (new QuestionnaireScorer())->calculate($answers);

        foreach ($allClients as $client) {
            Questionnaire::create([
                'client_id'    => $client->id,
                'token'        => Str::random(48),
                'sections'     => ['julia_ross', 'metabolique', 'diathese', 'ayurveda', 'hormones', 'canaris'],
                'answers'      => $answers,
                'scores'       => $scores,
                'submitted_at' => now()->subDays(rand(1, 30)),
                'is_active'    => true,
            ]);
        }

        // ── 1 invitation en attente ──────────────────────────────────────────
        Invitation::create([
            'email'      => 'invite-en-attente@dusk.test',
            'token'      => Str::uuid()->toString(),
            'invited_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->command->info('✓ DuskTestSeeder terminé');
        $this->command->info('  admin@dusk.test / conseiller1@dusk.test / conseiller2@dusk.test → Password1234!');
    }

    /**
     * Réponses représentatives pour obtenir des scores dans toutes les sections :
     * Chasseur B, Pitta dominant, jr3 + jr4 dépassés, D1 dominant, h1 élevé, Canari grade 1.
     */
    private function buildAnswers(): array
    {
        $a = [];

        // Métabolique — Chasseur B
        for ($i = 1; $i <= 37; $i++) {
            $a["mb{$i}"] = 'b';
        }
        $a['ms2'] = '1';
        $a['ms9'] = '1';

        // Ayurveda — Pitta dominant
        for ($i = 0; $i < 19; $i++) { $a["v{$i}"] = '3'; }
        for ($i = 0; $i < 20; $i++) { $a["p{$i}"] = '6'; }
        for ($i = 0; $i < 20; $i++) { $a["k{$i}"] = '2'; }

        // Julia Ross — jr3 Glycémie (seuil 15 → dépassé)
        $a['jr3_0'] = '1'; $a['jr3_1'] = '1'; $a['jr3_2'] = '1';
        $a['jr3_3'] = '1'; $a['jr3_5'] = '1'; $a['jr3_8'] = '1';

        // Julia Ross — jr4 Thyroïde (seuil 15 → dépassé)
        $a['jr4_0'] = '1'; $a['jr4_1'] = '1'; $a['jr4_3'] = '1'; $a['jr4_5'] = '1';

        // Diathèses — D1 dominant (colonnes 1 et 2)
        foreach (['d1a', 'd1b', 'd1c', 'd1d', 'd1e', 'd1f', 'd1g'] as $k) { $a[$k] = 'd1'; }
        foreach (['d2a', 'd2b', 'd2c', 'd2d', 'd2e', 'd2f', 'd2g'] as $k) { $a[$k] = 'd1'; }

        // Hormones — Progestérone élevée (h1)
        $a['h1_0'] = '1'; $a['h1_1'] = '1'; $a['h1_2'] = '1';
        $a['h1_4'] = '1'; $a['h1_6'] = '1'; $a['h1_8'] = '1';

        // Thyroïde hormonale
        $a['h4_0'] = '1'; $a['h4_2'] = '1'; $a['h4_3'] = '1';

        // Canaris — grade 1 (quelques items adulte)
        $a['ca1'] = '1'; $a['ca2'] = '1'; $a['ca3'] = '1';

        return $a;
    }
}
