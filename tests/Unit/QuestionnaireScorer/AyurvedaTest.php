<?php

namespace Tests\Unit\QuestionnaireScorer;

use App\Data\QuestionnaireData;
use App\Services\QuestionnaireScorer;
use PHPUnit\Framework\TestCase;

class AyurvedaTest extends TestCase
{
    private QuestionnaireScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new QuestionnaireScorer();
    }

    public function test_all_answers_at_6_gives_maximum_scores(): void
    {
        // vata : 19 questions × 6 = 114
        // pitta : 20 questions × 6 = 120
        // kapha : 20 questions × 6 = 120
        $answers = [];
        foreach (QuestionnaireData::$vata as $i => $_) {
            $answers['v' . $i] = 6;
        }
        foreach (QuestionnaireData::$pitta as $i => $_) {
            $answers['p' . $i] = 6;
        }
        foreach (QuestionnaireData::$kapha as $i => $_) {
            $answers['k' . $i] = 6;
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(114, $result['ayurveda']['vata']);
        $this->assertSame(120, $result['ayurveda']['pitta']);
        $this->assertSame(120, $result['ayurveda']['kapha']);
    }

    public function test_all_answers_at_1_gives_minimal_scores(): void
    {
        // vata : 19 × 1 = 19
        // pitta : 20 × 1 = 20
        // kapha : 20 × 1 = 20
        $answers = [];
        foreach (QuestionnaireData::$vata as $i => $_) {
            $answers['v' . $i] = 1;
        }
        foreach (QuestionnaireData::$pitta as $i => $_) {
            $answers['p' . $i] = 1;
        }
        foreach (QuestionnaireData::$kapha as $i => $_) {
            $answers['k' . $i] = 1;
        }

        $result = $this->scorer->calculate($answers);

        $this->assertSame(19, $result['ayurveda']['vata']);
        $this->assertSame(20, $result['ayurveda']['pitta']);
        $this->assertSame(20, $result['ayurveda']['kapha']);
    }

    public function test_vata_uses_keys_v0_to_v18(): void
    {
        // Seule v0 = 5, tout le reste à 0
        $answers = ['v0' => 5];

        $result = $this->scorer->calculate($answers);

        $this->assertSame(5, $result['ayurveda']['vata']);
        $this->assertSame(0, $result['ayurveda']['pitta']);
        $this->assertSame(0, $result['ayurveda']['kapha']);
    }

    public function test_pitta_uses_keys_p0_to_p19(): void
    {
        $answers = ['p0' => 3, 'p19' => 4];

        $result = $this->scorer->calculate($answers);

        $this->assertSame(0, $result['ayurveda']['vata']);
        $this->assertSame(7, $result['ayurveda']['pitta']);
        $this->assertSame(0, $result['ayurveda']['kapha']);
    }

    public function test_kapha_uses_keys_k0_to_k19(): void
    {
        $answers = ['k0' => 2, 'k19' => 6];

        $result = $this->scorer->calculate($answers);

        $this->assertSame(0, $result['ayurveda']['vata']);
        $this->assertSame(0, $result['ayurveda']['pitta']);
        $this->assertSame(8, $result['ayurveda']['kapha']);
    }

    public function test_missing_answers_give_score_zero_without_error(): void
    {
        $result = $this->scorer->calculate([]);

        $this->assertSame(0, $result['ayurveda']['vata']);
        $this->assertSame(0, $result['ayurveda']['pitta']);
        $this->assertSame(0, $result['ayurveda']['kapha']);
    }
}
