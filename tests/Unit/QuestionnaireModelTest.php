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
