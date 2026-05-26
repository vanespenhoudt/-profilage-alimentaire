<?php

namespace Tests\Unit\QuestionnaireScorer;

use App\Data\QuestionnaireData;
use App\Services\QuestionnaireScorer;
use PHPUnit\Framework\TestCase;

class JuliaRossTest extends TestCase
{
    private QuestionnaireScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new QuestionnaireScorer();
    }

    private function allCheckedAnswers(): array
    {
        $answers = [];
        foreach (QuestionnaireData::$julia_ross as $classe) {
            foreach ($classe['questions'] as $qi => $_) {
                $answers[$classe['id'] . '_' . $qi] = true;
            }
        }
        return $answers;
    }

    public function test_score_equals_sum_of_weights_of_checked_questions(): void
    {
        // jr1 : on coche uniquement les deux premières questions (w=4 et w=4 → total=8)
        $answers = ['jr1_0' => true, 'jr1_1' => true];

        $result = $this->scorer->calculate($answers);

        $this->assertSame(8, $result['julia_ross']['jr1']['total']);
    }

    public function test_depasse_is_true_when_total_exceeds_seuil(): void
    {
        // jr1 seuil = 10 : on coche suffisamment pour dépasser
        // On coche jr1_0 (w=4), jr1_1 (w=4), jr1_2 (w=4) → total=12 > 10
        $answers = ['jr1_0' => true, 'jr1_1' => true, 'jr1_2' => true];

        $result = $this->scorer->calculate($answers);

        $this->assertTrue($result['julia_ross']['jr1']['depasse']);
        $this->assertSame(12, $result['julia_ross']['jr1']['total']);
    }

    public function test_depasse_is_false_when_total_below_or_equal_seuil(): void
    {
        // jr1 seuil = 10 : on coche jr1_0 (w=4) et jr1_1 (w=4) → total=8 ≤ 10
        $answers = ['jr1_0' => true, 'jr1_1' => true];

        $result = $this->scorer->calculate($answers);

        $this->assertFalse($result['julia_ross']['jr1']['depasse']);
    }

    public function test_all_checked_always_exceeds_seuil(): void
    {
        $answers = $this->allCheckedAnswers();
        $result  = $this->scorer->calculate($answers);

        foreach (QuestionnaireData::$julia_ross as $classe) {
            $this->assertTrue(
                $result['julia_ross'][$classe['id']]['depasse'],
                "Classe {$classe['id']} devrait dépasser le seuil quand tout est coché"
            );
        }
    }

    public function test_no_answers_gives_score_zero_and_not_depasse(): void
    {
        $result = $this->scorer->calculate([]);

        foreach (QuestionnaireData::$julia_ross as $classe) {
            $this->assertSame(0, $result['julia_ross'][$classe['id']]['total']);
            $this->assertFalse($result['julia_ross'][$classe['id']]['depasse']);
        }
    }

    public function test_answer_keys_follow_pattern_classeid_underscore_index(): void
    {
        // jr3_5 → 6e question de jr3, w=4
        $classe = collect(QuestionnaireData::$julia_ross)->firstWhere('id', 'jr3');
        $weight = $classe['questions'][5]['w']; // index 5

        $answers = ['jr3_5' => true];
        $result  = $this->scorer->calculate($answers);

        $this->assertSame($weight, $result['julia_ross']['jr3']['total']);
    }
}
