<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EncryptionTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeConseiller(): User
    {
        return User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => true,
        ]);
    }

    private function makeClientFor(User $conseiller, array $overrides = []): Client
    {
        return Client::factory()->create(
            array_merge(['conseiller_id' => $conseiller->id], $overrides)
        );
    }

    // -----------------------------------------------------------------------
    // Client — chiffrement en base
    // -----------------------------------------------------------------------

    public function test_client_nom_is_not_stored_as_plaintext(): void
    {
        $client = $this->makeClientFor($this->makeConseiller(), ['nom' => 'Verstraeten']);

        $raw = DB::table('clients')->where('id', $client->id)->value('nom');

        $this->assertNotEquals('Verstraeten', $raw);
    }

    public function test_client_prenom_is_not_stored_as_plaintext(): void
    {
        $client = $this->makeClientFor($this->makeConseiller(), ['prenom' => 'Mathilde']);

        $raw = DB::table('clients')->where('id', $client->id)->value('prenom');

        $this->assertNotEquals('Mathilde', $raw);
    }

    public function test_client_tel_is_not_stored_as_plaintext(): void
    {
        $client = $this->makeClientFor($this->makeConseiller(), ['tel' => '+32 498 00 11 22']);

        $raw = DB::table('clients')->where('id', $client->id)->value('tel');

        $this->assertNotEquals('+32 498 00 11 22', $raw);
    }

    public function test_client_fields_decrypt_correctly_via_model(): void
    {
        $conseiller = $this->makeConseiller();
        $client = Client::factory()->create([
            'conseiller_id' => $conseiller->id,
            'nom'    => 'Verstraeten',
            'prenom' => 'Mathilde',
            'tel'    => '+32 498 00 11 22',
            'email'  => 'mathilde@test.be',
        ]);

        $fromDb = Client::find($client->id);

        $this->assertSame('Verstraeten', $fromDb->nom);
        $this->assertSame('Mathilde', $fromDb->prenom);
        $this->assertSame('+32 498 00 11 22', $fromDb->tel);
        $this->assertSame('mathilde@test.be', $fromDb->email);
    }

    public function test_all_pii_fields_are_not_stored_as_plaintext(): void
    {
        $conseiller = $this->makeConseiller();
        $client = Client::factory()->create([
            'conseiller_id' => $conseiller->id,
            'email'      => 'test@enc.be',
            'adresse'    => 'Rue de la Paix 1',
            'bt'         => 'A+',
            'notes'      => 'Notes confidentielles',
            'sexe'       => 'F',
            'sentinelles' => 'Fatigue chronique',
            'age'        => '42',
            'taille'     => '165',
            'poids'      => '62',
        ]);

        $raw = DB::table('clients')->where('id', $client->id)->first();

        $this->assertNotEquals('test@enc.be',            $raw->email);
        $this->assertNotEquals('Rue de la Paix 1',       $raw->adresse);
        $this->assertNotEquals('A+',                     $raw->bt);
        $this->assertNotEquals('Notes confidentielles',  $raw->notes);
        $this->assertNotEquals('F',                      $raw->sexe);
        $this->assertNotEquals('Fatigue chronique',      $raw->sentinelles);
        $this->assertNotEquals('42',                     $raw->age);
        $this->assertNotEquals('165',                    $raw->taille);
        $this->assertNotEquals('62',                     $raw->poids);
    }

    public function test_all_pii_fields_decrypt_correctly(): void
    {
        $conseiller = $this->makeConseiller();
        $client = Client::factory()->create([
            'conseiller_id' => $conseiller->id,
            'email'      => 'test@enc.be',
            'adresse'    => 'Rue de la Paix 1',
            'bt'         => 'A+',
            'notes'      => 'Notes confidentielles',
            'sexe'       => 'F',
            'sentinelles' => 'Fatigue chronique',
            'age'        => '42',
            'taille'     => '165',
            'poids'      => '62',
        ]);

        $fromDb = Client::find($client->id);

        $this->assertSame('test@enc.be',           $fromDb->email);
        $this->assertSame('Rue de la Paix 1',      $fromDb->adresse);
        $this->assertSame('A+',                    $fromDb->bt);
        $this->assertSame('Notes confidentielles', $fromDb->notes);
        $this->assertSame('F',                     $fromDb->sexe);
        $this->assertSame('Fatigue chronique',     $fromDb->sentinelles);
        $this->assertSame('42',                    $fromDb->age);
        $this->assertSame('165',                   $fromDb->taille);
        $this->assertSame('62',                    $fromDb->poids);
    }

    // -----------------------------------------------------------------------
    // Questionnaire — chiffrement answers et scores
    // -----------------------------------------------------------------------

    public function test_questionnaire_answers_are_not_stored_as_plaintext(): void
    {
        $client = $this->makeClientFor($this->makeConseiller());
        $q = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => 'tok-enc-answers',
            'answers'   => ['mb1' => 'a', 'mb2' => 'b'],
        ]);

        $raw = DB::table('questionnaires')->where('id', $q->id)->value('answers');

        $this->assertStringNotContainsString('"mb1"', $raw);
        $this->assertStringNotContainsString('"mb2"', $raw);
    }

    public function test_questionnaire_answers_decrypt_correctly(): void
    {
        $answers = ['mb1' => 'a', 'mb2' => 'b', 'jr3_3' => '1'];
        $client  = $this->makeClientFor($this->makeConseiller());

        $q = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => 'tok-dec-answers',
            'answers'   => $answers,
        ]);

        $this->assertSame($answers, Questionnaire::find($q->id)->answers);
    }

    public function test_questionnaire_scores_are_not_stored_as_plaintext(): void
    {
        $client = $this->makeClientFor($this->makeConseiller());
        $q = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => 'tok-enc-scores',
            'scores'    => ['metabolique' => ['a' => 2, 'b' => 5, 'type' => 'Chasseur B']],
        ]);

        $raw = DB::table('questionnaires')->where('id', $q->id)->value('scores');

        $this->assertStringNotContainsString('metabolique', $raw);
    }

    public function test_questionnaire_scores_decrypt_correctly(): void
    {
        $scores = ['metabolique' => ['a' => 2, 'b' => 5, 'type' => 'Chasseur B']];
        $client = $this->makeClientFor($this->makeConseiller());

        $q = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => 'tok-dec-scores',
            'scores'    => $scores,
        ]);

        $this->assertSame($scores, Questionnaire::find($q->id)->scores);
    }

    // -----------------------------------------------------------------------
    // Recherche PHP-side (champs chiffrés non requêtables en SQL)
    // -----------------------------------------------------------------------

    public function test_client_index_search_finds_by_nom_despite_encryption(): void
    {
        $conseiller = $this->makeConseiller();
        $this->makeClientFor($conseiller, ['nom' => 'Zupancic',  'prenom' => 'Alice']);
        $this->makeClientFor($conseiller, ['nom' => 'Borgloon',  'prenom' => 'Pierre']);

        $this->actingAs($conseiller)
            ->get(route('clients.index', ['search' => 'Zupancic']))
            ->assertOk()
            ->assertSee('Zupancic')
            ->assertDontSee('Borgloon');
    }

    public function test_client_index_search_finds_by_prenom(): void
    {
        $conseiller = $this->makeConseiller();
        $this->makeClientFor($conseiller, ['nom' => 'Zupancic', 'prenom' => 'Wulfric']);
        $this->makeClientFor($conseiller, ['nom' => 'Borgloon', 'prenom' => 'Ygraine']);

        $this->actingAs($conseiller)
            ->get(route('clients.index', ['search' => 'Wulfric']))
            ->assertOk()
            ->assertSee('Wulfric')
            ->assertDontSee('Ygraine');
    }

    public function test_client_index_search_is_case_insensitive(): void
    {
        $conseiller = $this->makeConseiller();
        $this->makeClientFor($conseiller, ['nom' => 'Zupancic', 'prenom' => 'Alice']);

        $this->actingAs($conseiller)
            ->get(route('clients.index', ['search' => 'zupancic']))
            ->assertOk()
            ->assertSee('Zupancic');
    }

    // -----------------------------------------------------------------------
    // Anonymisation
    // -----------------------------------------------------------------------

    public function test_anonymize_replaces_personal_data_and_redirects(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        $this->actingAs($conseiller)
            ->delete(route('clients.anonymize', $client))
            ->assertRedirect(route('clients.show', $client));

        $client->refresh();
        $this->assertSame('Anonymisé', $client->prenom);
        $this->assertSame('Anonymisé', $client->nom);
        $this->assertNull($client->tel);
        $this->assertNull($client->email);
        $this->assertNull($client->adresse);
        $this->assertNull($client->notes);
    }

    public function test_anonymize_is_forbidden_for_other_conseiller(): void
    {
        $conseiller1 = $this->makeConseiller();
        $conseiller2 = $this->makeConseiller();
        $client      = $this->makeClientFor($conseiller2);

        $this->actingAs($conseiller1)
            ->delete(route('clients.anonymize', $client))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // Commande clients:encrypt-existing
    // -----------------------------------------------------------------------

    public function test_encrypt_existing_command_encrypts_plaintext_data(): void
    {
        $conseiller = $this->makeConseiller();

        $id = DB::table('clients')->insertGetId([
            'code'          => 'CLI-ENC-TEST1',
            'conseiller_id' => $conseiller->id,
            'prenom'        => 'Sophie',
            'nom'           => 'Noel',
            'tel'           => '+32 470 00 00 00',
            'rgpd'          => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->artisan('clients:encrypt-existing')->assertSuccessful();

        $raw = DB::table('clients')->where('id', $id)->first();
        $this->assertNotEquals('Sophie', $raw->prenom);
        $this->assertNotEquals('Noel', $raw->nom);

        // Le modèle doit toujours déchiffrer vers les valeurs d'origine
        $client = Client::find($id);
        $this->assertSame('Sophie', $client->prenom);
        $this->assertSame('Noel', $client->nom);
    }

    public function test_encrypt_existing_command_is_idempotent(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller, ['nom' => 'Verstraeten', 'prenom' => 'Mathilde']);

        // Données déjà chiffrées par le modèle — relancer ne doit pas corrompre
        $this->artisan('clients:encrypt-existing')->assertSuccessful();

        $client->refresh();
        $this->assertSame('Verstraeten', $client->nom);
        $this->assertSame('Mathilde', $client->prenom);
    }
}
