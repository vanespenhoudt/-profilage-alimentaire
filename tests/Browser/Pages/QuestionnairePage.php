<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

/**
 * Page Object pour le questionnaire conseiller (/clients/{id}/questionnaire)
 * et le questionnaire public (/q/{token}).
 */
class QuestionnairePage extends Page
{
    private ?int    $clientId;
    private ?string $token;

    public function __construct(int $clientId = null, string $token = null)
    {
        $this->clientId = $clientId;
        $this->token    = $token;
    }

    public function url(): string
    {
        if ($this->token) {
            return "/q/{$this->token}";
        }

        return "/clients/{$this->clientId}/questionnaire";
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPresent('form')
                ->assertPresent('[data-section]');
    }

    public static function siteElements(): array
    {
        return [
            '@section-julia-ross'  => '#wrap-s1',
            '@section-metabolique' => '#wrap-s2',
            '@section-diathese'    => '#wrap-s3',
            '@section-ayurveda'    => '#wrap-s4',
            '@section-groupe-sang' => '#wrap-s5',
            '@section-hormones'    => '#wrap-s6',
            '@section-canaris'     => '#wrap-s7',
            '@save-status'         => '#saveStatus',
            '@save-toast'          => '#saveToast',
            '@menu-form'           => '#menuForm',
            '@aliments-text'       => 'textarea[name="aliments_text"]',
        ];
    }

    /**
     * Ouvre un accordion de section par son ID (s1..s7).
     */
    public function openSection(Browser $browser, int $sectionNumber): void
    {
        $browser->click("#wrap-s{$sectionNumber} .accordion-button")
                ->waitFor("#s{$sectionNumber}.show", 5);
    }

    /**
     * Remplit le champ texte jr_3_4_heures et attend l'autosave.
     */
    public function fillJr3HeuresAndWaitSave(Browser $browser, string $value): void
    {
        $browser->type('#jr_3_4_heures', $value)
                ->waitFor('@save-toast.show', 8);
    }

    /**
     * Injecte du contenu HTML dans le champ caché de TipTap (menu).
     */
    public function setMenuContent(Browser $browser, string $html): void
    {
        $browser->script("
            var hidden = document.querySelector('#menuForm textarea[name=\"menu_text\"]');
            if (hidden) { hidden.value = " . json_encode($html) . "; }
        ");
    }

    /**
     * Soumet le questionnaire public (RGPD + bouton soumettre).
     */
    public function submitPublicForm(Browser $browser): void
    {
        $browser->check('#rgpdConsent')
                ->press('Soumettre le questionnaire')
                ->waitForText('Merci', 10);
    }
}
