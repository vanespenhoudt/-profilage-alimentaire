<?php

namespace Tests\Unit\QuestionnaireScorer;

use App\Data\QuestionnaireData;
use App\Services\QuestionnaireScorer;
use PHPUnit\Framework\TestCase;

class HormonesTest extends TestCase
{
    private QuestionnaireScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new QuestionnaireScorer();
    }

    public function test_total_equals_number_of_checked_boxes_per_category(): void
    {
        // On coche toutes les questions de h1 (10 questions) et aucune autre
        $answers = [];
        $h1 = collect(QuestionnaireData::$hormones)->firstWhere('id', 'h1');
        foreach ($h1['questions'] as $qi => $_) {
            $answers['h1_' . $qi] = true;
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(10, $result['hormones']['h1']['total']);
        // Les autres catégories doivent être à 0
        foreach (['h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8'] as $cat) {
            $this->assertSame(0, $result['hormones'][$cat]['total']);
        }
    }

    public function test_max_is_preserved_in_score_result(): void
    {
        $result = $this->scorer->calculate([]);

        foreach (QuestionnaireData::$hormones as $cat) {
            $this->assertSame(
                $cat['max'],
                $result['hormones'][$cat['id']]['max'],
                "Max for category {$cat['id']} should match data definition"
            );
        }
    }

    public function test_answer_keys_follow_pattern_catid_underscore_index(): void
    {
        // h6 a 5 questions, on coche h6_3 (index 3)
        $answers = ['h6_3' => true];

        $result = $this->scorer->calculate($answers);

        $this->assertSame(1, $result['hormones']['h6']['total']);
    }

    public function test_no_answers_gives_zero_total_for_all_categories(): void
    {
        $result = $this->scorer->calculate([]);

        foreach (QuestionnaireData::$hormones as $cat) {
            $this->assertSame(0, $result['hormones'][$cat['id']]['total']);
        }
    }

    public function test_checking_all_questions_does_not_exceed_max(): void
    {
        // On coche toutes les réponses possibles
        $answers = [];
        foreach (QuestionnaireData::$hormones as $cat) {
            foreach ($cat['questions'] as $qi => $_) {
                $answers[$cat['id'] . '_' . $qi] = true;
            }
        }

        $result = $this->scorer->calculate($answers);

        foreach (QuestionnaireData::$hormones as $cat) {
            $catId = $cat['id'];
            $this->assertSame(
                count($cat['questions']),
                $result['hormones'][$catId]['total'],
                "Total for $catId should equal number of questions when all are checked"
            );
            // Le total ne doit pas dépasser le max déclaré
            $this->assertLessThanOrEqual(
                $cat['max'],
                $result['hormones'][$catId]['total'],
                "Total for $catId should not exceed max {$cat['max']}"
            );
        }
    }
}
