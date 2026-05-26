<?php

namespace Tests\Unit\QuestionnaireScorer;

use App\Data\QuestionnaireData;
use App\Services\QuestionnaireScorer;
use PHPUnit\Framework\TestCase;

class MetaboliqueTest extends TestCase
{
    private QuestionnaireScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new QuestionnaireScorer();
    }

    private function allBAnswers(): array
    {
        $answers = [];
        foreach (QuestionnaireData::$metabolique_binaire as $q) {
            $answers[$q['id']] = 'b';
        }
        return $answers;
    }

    private function allAAnswers(): array
    {
        $answers = [];
        foreach (QuestionnaireData::$metabolique_binaire as $q) {
            $answers[$q['id']] = 'a';
        }
        return $answers;
    }

    public function test_all_b_answers_gives_chasseur_b_type(): void
    {
        $answers = $this->allBAnswers();
        $result  = $this->scorer->calculate($answers);

        $this->assertSame('Chasseur B', $result['metabolique']['type']);
    }

    public function test_all_a_answers_gives_cueilleur_a_type(): void
    {
        $answers = $this->allAAnswers();
        $result  = $this->scorer->calculate($answers);

        $this->assertSame('Cueilleur A', $result['metabolique']['type']);
    }

    public function test_balanced_answers_below_threshold_gives_mixte_type(): void
    {
        // 19 A et 18 B → diff = 1 < 5 → Mixte
        $answers = [];
        $questions = QuestionnaireData::$metabolique_binaire;
        foreach ($questions as $i => $q) {
            $answers[$q['id']] = $i < 19 ? 'a' : 'b';
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame('Mixte', $result['metabolique']['type']);
    }

    public function test_symptomes_checked_count_towards_b_total(): void
    {
        // Toutes A pour les binaires → 37 A, 0 B
        // + on coche tous les symptômes (11) → B = 11
        // diff = |37 - 11| = 26 ≥ 5 → Cueilleur A
        $answers = $this->allAAnswers();
        foreach (QuestionnaireData::$metabolique_symptomes as $s) {
            $answers[$s['id']] = true;
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(37, $result['metabolique']['a']);
        $this->assertSame(11, $result['metabolique']['b']);
        $this->assertSame('Cueilleur A', $result['metabolique']['type']);
    }

    public function test_score_a_and_b_are_counted_correctly(): void
    {
        // 10 questions répondues A, 5 répondues B, reste vide
        $answers  = [];
        $questions = QuestionnaireData::$metabolique_binaire;
        foreach ($questions as $i => $q) {
            if ($i < 10) {
                $answers[$q['id']] = 'a';
            } elseif ($i < 15) {
                $answers[$q['id']] = 'b';
            }
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(10, $result['metabolique']['a']);
        $this->assertSame(5, $result['metabolique']['b']);
    }
}
