<?php

namespace Tests\Unit\QuestionnaireScorer;

use App\Data\QuestionnaireData;
use App\Services\QuestionnaireScorer;
use PHPUnit\Framework\TestCase;

class DiathèseTest extends TestCase
{
    private QuestionnaireScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new QuestionnaireScorer();
    }

    public function test_counts_c1_d1_correctly(): void
    {
        // On répond d1 sur toutes les questions de col1
        $answers = [];
        foreach (QuestionnaireData::$diathese_col1 as $q) {
            $answers[$q['id']] = 'd1';
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(7, $result['diathese']['c1_d1']);
        $this->assertSame(0, $result['diathese']['c1_d2']);
        $this->assertSame(0, $result['diathese']['c2_d1']);
        $this->assertSame(0, $result['diathese']['c2_d2']);
    }

    public function test_counts_c1_d2_correctly(): void
    {
        $answers = [];
        foreach (QuestionnaireData::$diathese_col1 as $q) {
            $answers[$q['id']] = 'd2';
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(0, $result['diathese']['c1_d1']);
        $this->assertSame(7, $result['diathese']['c1_d2']);
    }

    public function test_counts_c2_d1_correctly(): void
    {
        $answers = [];
        foreach (QuestionnaireData::$diathese_col2 as $q) {
            $answers[$q['id']] = 'd1';
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(0, $result['diathese']['c1_d1']);
        $this->assertSame(0, $result['diathese']['c1_d2']);
        $this->assertSame(7, $result['diathese']['c2_d1']);
        $this->assertSame(0, $result['diathese']['c2_d2']);
    }

    public function test_counts_c2_d2_correctly(): void
    {
        $answers = [];
        foreach (QuestionnaireData::$diathese_col2 as $q) {
            $answers[$q['id']] = 'd2';
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(0, $result['diathese']['c2_d1']);
        $this->assertSame(7, $result['diathese']['c2_d2']);
    }

    public function test_missing_answers_give_all_counters_to_zero(): void
    {
        $result = $this->scorer->calculate([]);

        $this->assertSame(0, $result['diathese']['c1_d1']);
        $this->assertSame(0, $result['diathese']['c1_d2']);
        $this->assertSame(0, $result['diathese']['c2_d1']);
        $this->assertSame(0, $result['diathese']['c2_d2']);
    }

    public function test_partial_answers_count_only_answered_questions(): void
    {
        // On répond uniquement à d1a=d1, d1b=d2, d2a=d1
        $answers = ['d1a' => 'd1', 'd1b' => 'd2', 'd2a' => 'd1'];

        $result = $this->scorer->calculate($answers);

        $this->assertSame(1, $result['diathese']['c1_d1']);
        $this->assertSame(1, $result['diathese']['c1_d2']);
        $this->assertSame(1, $result['diathese']['c2_d1']);
        $this->assertSame(0, $result['diathese']['c2_d2']);
    }
}
