<?php

namespace App\Actions\Questionnaire;

use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;

final class ValidateQuestionnaireAction
{
    private const LABELS = [
        'metabolique' => 'Typage Métabolique',
        'ayurveda'    => 'Ayurveda',
        'julia_ross'  => 'Julia Ross',
        'diathese'    => 'Diathèse de Ménétrier',
        'hormones'    => 'Bilan Hormonal',
    ];

    /**
     * Retourne les noms des sections suspectes (aucune réponse enregistrée).
     *
     * @return string[]
     */
    public function execute(Questionnaire $questionnaire): array
    {
        $answers  = $questionnaire->answers ?? [];
        $allSects = ['metabolique', 'ayurveda', 'julia_ross', 'diathese', 'hormones'];
        $sections = $questionnaire->sections ?? $allSects;

        if (empty($answers)) {
            return array_map(fn($s) => self::LABELS[$s] ?? $s, $sections);
        }

        $scores     = (new QuestionnaireScorer())->calculate($answers);
        $suspicious = [];

        foreach ($sections as $section) {
            if ($this->sectionIsEmpty($section, $scores)) {
                $suspicious[] = self::LABELS[$section] ?? $section;
            }
        }

        return $suspicious;
    }

    private function sectionIsEmpty(string $section, array $scores): bool
    {
        return match($section) {
            'metabolique' => ($scores['metabolique']['a'] + $scores['metabolique']['b']) === 0,
            'ayurveda'    => ($scores['ayurveda']['vata'] + $scores['ayurveda']['pitta'] + $scores['ayurveda']['kapha']) === 0,
            'julia_ross'  => array_sum(array_column(array_values($scores['julia_ross']), 'total')) === 0,
            'diathese'    => array_sum($scores['diathese']) === 0,
            'hormones'    => array_sum(array_column(array_values($scores['hormones']), 'total')) === 0,
            default       => false,
        };
    }
}
