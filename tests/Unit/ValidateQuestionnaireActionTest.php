<?php

namespace Tests\Unit;

use App\Actions\Questionnaire\ValidateQuestionnaireAction;
use App\Models\Questionnaire;
use Tests\TestCase;

class ValidateQuestionnaireActionTest extends TestCase
{
    private ValidateQuestionnaireAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ValidateQuestionnaireAction();
    }

    // Crée un Questionnaire en mémoire (sans DB) avec les attributs donnés.
    private function makeQuestionnaire(array $answers = [], ?array $sections = null): Questionnaire
    {
        $q           = new Questionnaire();
        $q->answers  = $answers;
        $q->sections = $sections;
        return $q;
    }

    // Réponses minimales couvrant les 6 sections (score > 0 dans chaque)
    private function answersAllSections(): array
    {
        return [
            'mb1'            => 'a',  // Métaboltyping
            'v0'             => '3',  // Ayurveda
            'jr1_0'          => '1',  // Julia Ross — Neurotransmetteurs
            'd1a'            => 'd1', // Diathèse
            'groupe_sanguin' => 'O',  // Groupe sanguin
            'h1_0'           => '1',  // Bilan Hormonal
        ];
    }

    // -----------------------------------------------------------------------
    // Aucune réponse
    // -----------------------------------------------------------------------

    public function test_toutes_les_sections_sont_suspectes_quand_aucune_reponse(): void
    {
        $q = $this->makeQuestionnaire([]);

        $result = $this->action->execute($q);

        $this->assertCount(6, $result);
        $this->assertContains('Métaboltyping', $result);
        $this->assertContains('Ayurveda', $result);
        $this->assertContains('Julia Ross — Neurotransmetteurs', $result);
        $this->assertContains('Diathèses', $result);
        $this->assertContains('Groupe sanguin', $result);
        $this->assertContains('Bilan Hormonal', $result);
    }

    // -----------------------------------------------------------------------
    // Réponses complètes
    // -----------------------------------------------------------------------

    public function test_aucune_section_suspecte_quand_toutes_les_sections_ont_des_reponses(): void
    {
        $q = $this->makeQuestionnaire($this->answersAllSections());

        $result = $this->action->execute($q);

        $this->assertEmpty($result);
    }

    // -----------------------------------------------------------------------
    // Une seule section manquante
    // -----------------------------------------------------------------------

    public function test_seule_la_section_sans_reponse_est_suspecte(): void
    {
        // Toutes les sections sauf Ayurveda
        $answers = [
            'mb1'            => 'a',
            'jr1_0'          => '1',
            'd1a'            => 'd1',
            'groupe_sanguin' => 'A',
            'h1_0'           => '1',
        ];

        $q      = $this->makeQuestionnaire($answers);
        $result = $this->action->execute($q);

        $this->assertCount(1, $result);
        $this->assertContains('Ayurveda', $result);
        $this->assertNotContains('Métaboltyping', $result);
    }

    // -----------------------------------------------------------------------
    // Filtre sections
    // -----------------------------------------------------------------------

    public function test_respecte_le_filtre_sections_du_questionnaire(): void
    {
        // Questionnaire envoyé seulement avec metabolique et diathese
        $q = $this->makeQuestionnaire(
            answers:  ['mb1' => 'a'],       // metabolique OK
            sections: ['metabolique', 'diathese']
        );

        $result = $this->action->execute($q);

        // metabolique a une réponse → pas suspect
        // diathese n'en a pas → suspect
        $this->assertCount(1, $result);
        $this->assertContains('Diathèses', $result);
        $this->assertNotContains('Ayurveda', $result);
        $this->assertNotContains('Julia Ross — Neurotransmetteurs', $result);
        $this->assertNotContains('Bilan Hormonal', $result);
    }

    public function test_retourne_tableau_vide_si_sections_vide(): void
    {
        $q = $this->makeQuestionnaire(answers: [], sections: []);

        $result = $this->action->execute($q);

        $this->assertEmpty($result);
    }

    // -----------------------------------------------------------------------
    // Sections individuelles
    // -----------------------------------------------------------------------

    public function test_metabolique_suspect_quand_aucune_reponse_ab(): void
    {
        // Seul metabolique en scope, aucune réponse A/B
        $q = $this->makeQuestionnaire(
            answers:  [],
            sections: ['metabolique']
        );

        $result = $this->action->execute($q);

        $this->assertContains('Métaboltyping', $result);
    }

    public function test_ayurveda_non_suspect_quand_un_score_vata_non_zero(): void
    {
        $q = $this->makeQuestionnaire(
            answers:  ['v0' => '1'],
            sections: ['ayurveda']
        );

        $result = $this->action->execute($q);

        $this->assertNotContains('Ayurveda', $result);
    }

    public function test_diathese_suspect_quand_score_total_zero(): void
    {
        $q = $this->makeQuestionnaire(
            answers:  [],
            sections: ['diathese']
        );

        $result = $this->action->execute($q);

        $this->assertContains('Diathèses', $result);
    }
}
