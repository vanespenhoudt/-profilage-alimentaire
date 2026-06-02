<?php

namespace Tests\Unit;

use App\Models\Questionnaire;
use Carbon\Carbon;
use Tests\TestCase;

class QuestionnaireModelTest extends TestCase
{
    /**
     * Crée une instance Questionnaire sans persister, en forçant les attributs
     * dans le tableau interne pour éviter les casts Eloquent qui appellent la
     * connexion DB.
     */
    private function makeQuestionnaire(array $attributes = []): Questionnaire
    {
        $q = new Questionnaire();
        // setRawAttributes bypasse les casts et ne nécessite pas de connexion DB
        $q->setRawAttributes($attributes, true);
        return $q;
    }

    // --- isSubmitted() ---

    public function test_is_submitted_returns_false_when_submitted_at_is_null(): void
    {
        $q = $this->makeQuestionnaire(['submitted_at' => null]);
        $this->assertFalse($q->isSubmitted());
    }

    public function test_is_submitted_returns_true_when_submitted_at_is_set(): void
    {
        $q = $this->makeQuestionnaire(['submitted_at' => Carbon::now()]);
        $this->assertTrue($q->isSubmitted());
    }

    // --- mergeAnswers() ---

    public function test_merge_answers_adds_new_section_to_empty_existing(): void
    {
        $result = Questionnaire::mergeAnswers([], ['mb1' => 'a', 'mb2' => 'b']);

        $this->assertSame(['mb1' => 'a', 'mb2' => 'b'], $result);
    }

    public function test_merge_answers_preserves_untouched_sections(): void
    {
        $existing = ['mb1' => 'a', 'jr3_0' => '1', 'jr3_1' => '1'];
        $incoming = ['mb1' => 'b']; // seule la section métabolique est soumise

        $result = Questionnaire::mergeAnswers($existing, $incoming);

        $this->assertSame('b',  $result['mb1']);
        $this->assertSame('1',  $result['jr3_0']); // julia_ross préservée
        $this->assertSame('1',  $result['jr3_1']);
    }

    public function test_merge_answers_removes_unchecked_checkboxes_in_same_section(): void
    {
        $existing = ['mb1' => 'a', 'mb2' => 'b', 'ms1' => '1'];
        $incoming = ['mb1' => 'a']; // mb2 et ms1 décochés (absent du POST)

        $result = Questionnaire::mergeAnswers($existing, $incoming);

        $this->assertArrayHasKey('mb1', $result);
        $this->assertArrayNotHasKey('mb2', $result);
        $this->assertArrayNotHasKey('ms1', $result);
    }

    public function test_merge_answers_handles_multiple_sections_simultaneously(): void
    {
        $existing = ['mb1' => 'a', 'jr3_0' => '1'];
        $incoming = ['mb1' => 'b', 'jr3_0' => '1', 'jr3_1' => '1'];

        $result = Questionnaire::mergeAnswers($existing, $incoming);

        $this->assertSame('b',  $result['mb1']);
        $this->assertSame('1',  $result['jr3_0']);
        $this->assertSame('1',  $result['jr3_1']);
    }

    public function test_merge_answers_handles_identite_prefix(): void
    {
        $existing = ['identite_nom' => 'Ancien', 'mb1' => 'a'];
        $incoming = ['identite_nom' => 'Nouveau', 'identite_prenom' => 'Marie'];

        $result = Questionnaire::mergeAnswers($existing, $incoming);

        $this->assertSame('Nouveau', $result['identite_nom']);
        $this->assertSame('Marie',   $result['identite_prenom']);
        $this->assertSame('a',       $result['mb1']); // métabolique préservé
    }

    // --- statusLabel() ---

    public function test_status_label_returns_en_attente_when_no_answers_and_not_submitted(): void
    {
        $q = $this->makeQuestionnaire(['submitted_at' => null, 'answers' => null]);
        $this->assertSame('En attente', $q->statusLabel());
    }

    public function test_status_label_returns_en_cours_when_answers_present_but_not_submitted(): void
    {
        // answers doit être une chaîne JSON car setRawAttributes bypass les casts
        $q = $this->makeQuestionnaire(['submitted_at' => null, 'answers' => json_encode(['mb1' => 'a'])]);
        $this->assertSame('En cours', $q->statusLabel());
    }

    public function test_status_label_contains_soumis_le_when_submitted_at_is_set(): void
    {
        $date = Carbon::create(2026, 5, 25, 14, 30, 0);
        $q    = $this->makeQuestionnaire(['submitted_at' => $date]);
        $this->assertStringContainsString('Soumis le', $q->statusLabel());
    }
}
