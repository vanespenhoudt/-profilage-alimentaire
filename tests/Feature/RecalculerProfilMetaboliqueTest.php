<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecalculerProfilMetaboliqueTest extends TestCase
{
    use RefreshDatabase;

    private function questionnaireAvecScores(array $met, string $type): Questionnaire
    {
        $client = Client::factory()->create(['conseiller_id' => User::factory()->create()->id]);

        return Questionnaire::create([
            'client_id' => $client->id,
            'is_active' => true,
            'answers'   => ['groupe_sanguin' => 'A'],
            'scores'    => [
                'metabolique'    => $met + ['type' => $type],
                'metabolic_type' => $type === 'Mixte' ? 'Mixte' : explode(' ', $type)[0],
                'julia_ross'     => ['jr1' => ['total' => 12, 'seuil' => 10, 'depasse' => true]],
            ],
        ]);
    }

    public function test_corrige_le_profil_sans_toucher_aux_autres_scores(): void
    {
        $q = $this->questionnaireAvecScores(['a' => 5, 'b' => 16, 'm' => 19], 'Mixte');

        $this->artisan('metabolique:recalculer-profil')
            ->expectsOutputToContain('1 bilan(s) modifié(s)')
            ->assertSuccessful();

        $scores = $q->fresh()->scores;
        $this->assertSame('Chasseur B', $scores['metabolique']['type']);
        $this->assertSame('Chasseur', $scores['metabolic_type']);
        $this->assertSame([5, 16, 19], [$scores['metabolique']['a'], $scores['metabolique']['b'], $scores['metabolique']['m']]);
        $this->assertSame(['jr1' => ['total' => 12, 'seuil' => 10, 'depasse' => true]], $scores['julia_ross']);
    }

    public function test_dry_run_ne_modifie_rien(): void
    {
        $q = $this->questionnaireAvecScores(['a' => 5, 'b' => 16, 'm' => 19], 'Mixte');

        $this->artisan('metabolique:recalculer-profil', ['--dry-run' => true])
            ->expectsOutputToContain('1 bilan(s) à modifier')
            ->assertSuccessful();

        $this->assertSame('Mixte', $q->fresh()->scores['metabolic_type']);
    }

    public function test_profil_deja_correct_inchange(): void
    {
        $this->questionnaireAvecScores(['a' => 10, 'b' => 14, 'm' => 20], 'Mixte');

        $this->artisan('metabolique:recalculer-profil')
            ->expectsOutputToContain('Aucun profil ne change')
            ->assertSuccessful();
    }
}
