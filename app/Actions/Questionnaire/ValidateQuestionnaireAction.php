<?php

namespace App\Actions\Questionnaire;

use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;

final class ValidateQuestionnaireAction
{
    private const LABELS = [
        'julia_ross'     => 'Julia Ross — Neurotransmetteurs',
        'metabolique'    => 'Métaboltyping',
        'diathese'       => 'Diathèses',
        'ayurveda'       => 'Ayurveda',
        'groupe_sanguin' => 'Groupe sanguin',
        'hormones'       => 'Bilan Hormonal',
    ];

    /**
     * Retourne les noms des sections suspectes (aucune réponse enregistrée).
     *
     * @return string[]
     */
    public function execute(Questionnaire $questionnaire): array
    {
        $answers  = $questionnaire->answers ?? [];
        $allSects = ['julia_ross', 'metabolique', 'diathese', 'ayurveda', 'groupe_sanguin', 'hormones'];
        $sections = $questionnaire->sections ?? $allSects;

        if (empty($answers)) {
            return array_map(fn($s) => self::LABELS[$s] ?? $s, $sections);
        }

        $scores     = (new QuestionnaireScorer())->calculate($answers);
        $suspicious = [];

        foreach ($sections as $section) {
            if ($this->sectionIsEmpty($section, $scores, $answers)) {
                $suspicious[] = self::LABELS[$section] ?? $section;
            }
        }

        return $suspicious;
    }

    private function sectionIsEmpty(string $section, array $scores, array $answers = []): bool
    {
        return match($section) {
            'metabolique'    => ($scores['metabolique']['a'] + $scores['metabolique']['b']) === 0,
            'ayurveda'       => ($scores['ayurveda']['vata'] + $scores['ayurveda']['pitta'] + $scores['ayurveda']['kapha']) === 0,
            'julia_ross'     => array_sum(array_column(array_values($scores['julia_ross']), 'total')) === 0,
            'diathese'       => array_sum($scores['diathese']) === 0,
            'hormones'       => array_sum(array_column(array_values($scores['hormones']), 'total')) === 0,
            'groupe_sanguin' => empty($answers['groupe_sanguin']),
            default          => false,
        };
    }
}
