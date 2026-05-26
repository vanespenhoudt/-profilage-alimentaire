<?php

namespace Tests\Unit;

use App\Data\QuestionnaireData;
use PHPUnit\Framework\TestCase;

class QuestionnaireDataTest extends TestCase
{
    // --- metabolique_binaire ---

    public function test_metabolique_binaire_has_exactly_37_elements(): void
    {
        $this->assertCount(37, QuestionnaireData::$metabolique_binaire);
    }

    public function test_metabolique_binaire_each_element_has_required_keys(): void
    {
        foreach (QuestionnaireData::$metabolique_binaire as $index => $question) {
            $this->assertArrayHasKey('id', $question, "Element $index missing key 'id'");
            $this->assertArrayHasKey('label', $question, "Element $index missing key 'label'");
            $this->assertArrayHasKey('a', $question, "Element $index missing key 'a'");
            $this->assertArrayHasKey('b', $question, "Element $index missing key 'b'");
        }
    }

    // --- metabolique_symptomes ---

    public function test_metabolique_symptomes_has_exactly_11_elements(): void
    {
        $this->assertCount(11, QuestionnaireData::$metabolique_symptomes);
    }

    // --- ayurveda dosha ---

    public function test_vata_has_exactly_19_elements(): void
    {
        $this->assertCount(19, QuestionnaireData::$vata);
    }

    public function test_pitta_has_exactly_20_elements(): void
    {
        $this->assertCount(20, QuestionnaireData::$pitta);
    }

    public function test_kapha_has_exactly_20_elements(): void
    {
        $this->assertCount(20, QuestionnaireData::$kapha);
    }

    // --- julia_ross ---

    public function test_julia_ross_has_exactly_8_classes(): void
    {
        $this->assertCount(8, QuestionnaireData::$julia_ross);
    }

    public function test_julia_ross_each_class_has_required_keys(): void
    {
        foreach (QuestionnaireData::$julia_ross as $index => $classe) {
            $this->assertArrayHasKey('id', $classe, "Classe $index missing key 'id'");
            $this->assertArrayHasKey('titre', $classe, "Classe $index missing key 'titre'");
            $this->assertArrayHasKey('seuil', $classe, "Classe $index missing key 'seuil'");
            $this->assertArrayHasKey('questions', $classe, "Classe $index missing key 'questions'");
        }
    }

    public function test_julia_ross_each_question_has_t_and_w_keys_with_positive_weight(): void
    {
        foreach (QuestionnaireData::$julia_ross as $classe) {
            foreach ($classe['questions'] as $qi => $question) {
                $this->assertArrayHasKey('t', $question, "Question $qi in {$classe['id']} missing key 't'");
                $this->assertArrayHasKey('w', $question, "Question $qi in {$classe['id']} missing key 'w'");
                $this->assertGreaterThan(0, $question['w'], "Weight of question $qi in {$classe['id']} must be > 0");
            }
        }
    }

    // --- diathese ---

    public function test_diathese_col1_has_exactly_7_elements(): void
    {
        $this->assertCount(7, QuestionnaireData::$diathese_col1);
    }

    public function test_diathese_col2_has_exactly_7_elements(): void
    {
        $this->assertCount(7, QuestionnaireData::$diathese_col2);
    }

    // --- hormones ---

    public function test_hormones_has_exactly_8_categories(): void
    {
        $this->assertCount(8, QuestionnaireData::$hormones);
    }

    public function test_hormones_each_category_has_required_keys(): void
    {
        foreach (QuestionnaireData::$hormones as $index => $cat) {
            $this->assertArrayHasKey('id', $cat, "Category $index missing key 'id'");
            $this->assertArrayHasKey('titre', $cat, "Category $index missing key 'titre'");
            $this->assertArrayHasKey('max', $cat, "Category $index missing key 'max'");
            $this->assertArrayHasKey('questions', $cat, "Category $index missing key 'questions'");
        }
    }
}
