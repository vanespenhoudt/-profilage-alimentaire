<?php

namespace Tests\Unit\QuestionnaireScorer;

use App\Services\QuestionnaireScorer;
use PHPUnit\Framework\TestCase;

class InterpretationTest extends TestCase
{
    private QuestionnaireScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new QuestionnaireScorer();
    }

    // -----------------------------------------------------------------------
    // interpretMetabolic
    // -----------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\DataProvider('metabolicProvider')]
    public function test_interpret_metabolic(int $chasseur, int $cueilleur, int $mixte, string $expected): void
    {
        $result = $this->scorer->interpretMetabolic([
            'chasseur'  => $chasseur,
            'cueilleur' => $cueilleur,
            'mixte'     => $mixte,
        ]);

        $this->assertSame($expected, $result);
    }

    public static function metabolicProvider(): array
    {
        return [
            'chasseur dominant'      => [30, 22, 5,  'Chasseur'],
            'ecart insuffisant'      => [24, 21, 8,  'Mixte'],
            'cueilleur dominant'     => [18, 30, 10, 'Cueilleur'],
            'egalite'                => [20, 20, 15, 'Mixte'],
            'ecart exactement 5'     => [27, 22, 0,  'Chasseur'],
            'cueilleur ecart exact'  => [10, 20, 0,  'Cueilleur'],
            'ecart 4 reste mixte'    => [26, 22, 0,  'Mixte'],
        ];
    }

    public function test_interpret_metabolic_zero_scores_returns_mixte(): void
    {
        $result = $this->scorer->interpretMetabolic(['chasseur' => 0, 'cueilleur' => 0, 'mixte' => 0]);

        $this->assertSame('Mixte', $result);
    }

    public function test_calculate_includes_metabolic_type(): void
    {
        $answers = [];
        $scores  = $this->scorer->calculate($answers);

        $this->assertArrayHasKey('metabolic_type', $scores);
        $this->assertContains($scores['metabolic_type'], ['Chasseur', 'Cueilleur', 'Mixte']);
    }

    // -----------------------------------------------------------------------
    // interpretAyurveda
    // -----------------------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\DataProvider('ayurvedaProvider')]
    public function test_interpret_ayurveda(int $vata, int $pitta, int $kapha, string $expected): void
    {
        $result = $this->scorer->interpretAyurveda([
            'vata'  => $vata,
            'pitta' => $pitta,
            'kapha' => $kapha,
        ]);

        $this->assertSame($expected, $result);
    }

    public static function ayurvedaProvider(): array
    {
        return [
            'vata seul'         => [84, 45,  30, 'Vata'],
            'vata-pitta double' => [84, 78,  30, 'Vata-Pitta'],
            'pitta-kapha double'=> [40, 80,  72, 'Pitta-Kapha'],
            'tridosha'          => [72, 68,  65, 'Tridosha'],
            'pitta-kapha 2'     => [50, 85,  80, 'Pitta-Kapha'],
            'vata-pitta egal'   => [70, 70,  40, 'Vata-Pitta'],
            'pitta seul'        => [30, 90,  20, 'Pitta'],
            'kapha seul'        => [20, 30, 100, 'Kapha'],
        ];
    }

    public function test_interpret_ayurveda_tridosha_when_ecart_12(): void
    {
        // max - min = 12 => Tridosha
        $result = $this->scorer->interpretAyurveda(['vata' => 62, 'pitta' => 57, 'kapha' => 50]);

        $this->assertSame('Tridosha', $result);
    }

    public function test_interpret_ayurveda_double_when_ecart_12_between_first_and_second(): void
    {
        // max-min > 12, max-second = 12 => double dosha
        $result = $this->scorer->interpretAyurveda(['vata' => 80, 'pitta' => 68, 'kapha' => 40]);

        $this->assertSame('Vata-Pitta', $result);
    }

    public function test_calculate_includes_ayurveda_type(): void
    {
        $answers = [];
        $scores  = $this->scorer->calculate($answers);

        $this->assertArrayHasKey('ayurveda_type', $scores);
        $valid = ['Vata', 'Pitta', 'Kapha', 'Vata-Pitta', 'Pitta-Vata', 'Pitta-Kapha',
                  'Kapha-Pitta', 'Vata-Kapha', 'Kapha-Vata', 'Tridosha'];
        $this->assertContains($scores['ayurveda_type'], $valid);
    }
}
