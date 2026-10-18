<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BilanAffichageTest extends TestCase
{
    use RefreshDatabase;

    private User $conseiller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conseiller = User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
    }

    private function bilan(array $client = [], array $questionnaire = [])
    {
        $c = Client::factory()->create(['conseiller_id' => $this->conseiller->id] + $client);
        Questionnaire::create([
            'client_id' => $c->id,
            'is_active' => true,
            // Classe 6 entièrement cochée (dépassée) + une réponse progestérone
            'answers'   => ['groupe_sanguin' => 'A', 'h1_0' => '1'] + array_fill_keys(array_map(fn ($i) => "jr6_{$i}", range(0, 19)), '1'),
        ] + $questionnaire);

        return $this->actingAs($this->conseiller)->get(route('questionnaire.bilan', $c))->assertOk();
    }

    public function test_homme_classe_6_et_progesterone_non_applicables(): void
    {
        $this->bilan(['sexe' => 'M'])
            ->assertSee('Non applicable (homme)')
            ->assertDontSee('Possible déséquilibre hormonal.'); // guide de la classe 6 absent
        $this->assertSame(2, substr_count($this->bilan(['sexe' => 'Homme'])->getContent(), 'Non applicable (homme)'));
    }

    public function test_femme_classe_6_et_progesterone_affichees(): void
    {
        $this->bilan(['sexe' => 'F'])
            ->assertDontSee('Non applicable (homme)')
            ->assertSee('Possible déséquilibre hormonal.');
    }

    public function test_sexe_inconnu_tout_est_affiche(): void
    {
        $this->bilan(['sexe' => null])->assertDontSee('Non applicable (homme)');
    }

    public function test_les_10_aliments_preferes_sont_tous_affiches(): void
    {
        $aliments = "Poulet\nSaumon\nAvocat\nOeufs\nFromage\nPommes\nChocolat noir\nSoupe maison\nRiz blanc basmati\nRiz complet";

        $response = $this->bilan([], ['aliments_text' => $aliments])->assertSee('10 aliments préférés');
        foreach (explode("\n", $aliments) as $aliment) {
            $response->assertSee($aliment);
        }
        $response->assertDontSee('<textarea rows="6" class="form-control" readonly', false);
    }

    public function test_menu_titre_3_journees_et_retours_a_la_ligne_conserves(): void
    {
        $this->bilan([], ['menu_text' => "Jour 1 : soupe\nJour 2 : pâtes\nJour 3 : riz"])
            ->assertSee('Menu 3 journées')
            ->assertDontSee('Menu 5 jours')
            ->assertSee('Jour 1 : soupe<br />', false);
    }

    public function test_menu_client_ne_peut_pas_injecter_de_script(): void
    {
        $this->bilan([], ['menu_text' => '<p onclick="alert(1)">Jour 1</p><script>alert(2)</script>'])
            ->assertSee('<p>Jour 1</p>', false)
            ->assertDontSee('<script>alert(2)</script>', false)
            ->assertDontSee('onclick="alert(1)"', false);
    }
}
