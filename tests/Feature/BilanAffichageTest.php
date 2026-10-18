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
        Questionnaire::create($questionnaire + [
            'client_id' => $c->id,
            'is_active' => true,
            // Classe 6 entièrement cochée (dépassée) + une réponse progestérone
            'answers'   => ['groupe_sanguin' => 'A', 'h1_0' => '1'] + array_fill_keys(array_map(fn ($i) => "jr6_{$i}", range(0, 19)), '1'),
        ]);

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

    private function diathese(string $val): array
    {
        return array_fill_keys(['d1a', 'd1b', 'd1c', 'd1d', 'd2a', 'd2b', 'd2c'], $val) + ['groupe_sanguin' => 'A'];
    }

    public function test_diathese_d1_chene_et_guide_terrain_robuste(): void
    {
        $this->bilan([], ['answers' => $this->diathese('d1')])
            ->assertSee('Chêne — terrain robuste (énergie naturelle, récupération rapide)')
            ->assertSee('Guide d\'interprétation — Terrain robuste', false)
            ->assertSee('Cures courtes : 15 jours maximum')
            ->assertSee('Exercice dense en complément (jogging, tennis, arts martiaux).')
            ->assertDontSee('Passer directement')
            ->assertDontSee('terrain allergique')
            ->assertSee('Source : Taty Lauwers, livret diathèse — Choisir les aliments qu\'il me faut', false);
    }

    public function test_diathese_d2_roseau_et_guide_terrain_fragilise(): void
    {
        $this->bilan([], ['answers' => $this->diathese('d2')])
            ->assertSee('Roseau — terrain à ménager (énergie à construire, récupération lente, besoin de repos)')
            ->assertSee('Terrain fragilisé')
            ->assertSee('Entrée en cure progressive, sur quelques semaines ; pas de cure excessive (ex. tout cru).')
            ->assertSee('Attention à ne pas surprotéiner le quotidien.')
            ->assertSee('Exercice doux (stretching, yoga, taichi) ; repos prioritaire.')
            ->assertSee('Besoin d\'un suivi encadré, avec des rendez-vous réguliers.')
            ->assertDontSee('terrain déficitaire');
    }
}
