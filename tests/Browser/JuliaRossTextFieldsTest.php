<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class JuliaRossTextFieldsTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function makeQuestionnaire(): array
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $token      = Str::random(48);

        $q = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => $token,
            'sections'  => ['julia_ross'],
        ]);

        return [$token, $q];
    }

    // -----------------------------------------------------------------------
    // Présence des champs
    // -----------------------------------------------------------------------

    public function test_jr3_heures_field_is_present_in_julia_ross_section(): void
    {
        [$token] = $this->makeQuestionnaire();

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->assertPresent('#jr_3_4_heures');
        });
    }

    public function test_jr5_type_and_diagnostic_fields_are_present(): void
    {
        [$token] = $this->makeQuestionnaire();

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->assertPresent('#jr_5_10_type')
                ->assertPresent('#jr_5_10_diagnostic');
        });
    }

    // -----------------------------------------------------------------------
    // Persistance via autosave
    // -----------------------------------------------------------------------

    public function test_jr3_heures_is_saved_by_autosave_and_persists_after_reload(): void
    {
        [$token] = $this->makeQuestionnaire();

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->type('#jr_3_4_heures', 'après le dîner')
                ->waitFor('#saveToast.show', 8)
                ->refresh()
                ->waitFor('#jr_3_4_heures', 5)
                ->assertInputValue('#jr_3_4_heures', 'après le dîner');
        });
    }

    public function test_jr5_fields_are_saved_and_persist_after_reload(): void
    {
        [$token] = $this->makeQuestionnaire();

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                // Saisir les deux champs avant que le debounce 2s ne s'écoule
                // → un seul appel autosave couvre les deux valeurs
                ->type('#jr_5_10_type', 'allergie réelle')
                ->type('#jr_5_10_diagnostic', 'test sanguin')
                ->waitFor('#saveToast.show', 8)
                ->refresh()
                ->waitFor('#jr_5_10_type', 5)
                ->assertInputValue('#jr_5_10_type', 'allergie réelle')
                ->assertInputValue('#jr_5_10_diagnostic', 'test sanguin');
        });
    }
}
